<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    filter_mapofusers
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace filter_mapofusers;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use html_writer;

/**
 * Implementation of the Moodle filter API for the Map of users filter.
 *
 * @package    filter_mapofusers
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (class_exists('\core_filters\text_filter')) {
    class_alias('\core_filters\text_filter', 'mapofusers_base_text_filter');
} else {
    class_alias('\moodle_text_filter', 'mapofusers_base_text_filter');
}

class text_filter extends \mapofusers_base_text_filter {

    const TOKEN = '{{ mapofusers ';

    /**
     * @var null
     */
    private $locationdata = null;
    private $config = null;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->config = get_config('filter_mapofusers');
    }

    /**
     * Function called by Moodle.
     */
    public function filter($text, array $options = array()) {
        global $CFG, $PAGE;

        if (empty($text) or is_numeric($text)) {
            return $text;
        }

        if (strpos($text, self::TOKEN) !== false) {
            return $this->apply($text);
        } else {
            return $text;
        }
    }


    /**
     * Does the actual filtering.
     *
     * @param string $text
     * @return string
     */
    protected function apply($text) {

        // Split text into parts, keeping delimiter.
        $regex = '@(?=' . self::TOKEN . ')@';
        $parts = preg_split($regex, $text);

        foreach ($parts as $key => $part) {

            if (strpos($part, self::TOKEN) === 0) {

                $atoms = explode(' }}', $part);

                // Check filter integrity.
                if (count($atoms) == 2) {

                    // Replace filter code with filter content.
                    $atoms[0] = $this->get_map($atoms[0]);
                    $parts[$key] = implode($atoms);

                // Show error.
                } else {
                    return $this->return_error(get_string('errormsg', 'filter_mapofusers'), $text);
                }
            }
        }

        // Reassemble parts.
        return implode($parts);
    }


    /**
     * Returns a map of user's locations using leaflet.
     *
     * @param array $courseids
     * @param array $categoryids
     * @param string $fields
     * @param string $sort
     * @return array $courses
     *
     */
    protected function get_map($text) {

        global $CFG, $DB, $PAGE;

        // Get all users in a specific course.
        if (!strpos($text, 'all') && $PAGE->context && $PAGE->context instanceof context_course) {
            $context = $PAGE->context;
            $users = get_enrolled_users($context);
        }

        // Get all users in the system.
        if (!isset($context)) {
            $users = $DB->get_records_select('user', "deleted = 0 AND suspended = 0 AND confirmed = 1 AND username <> 'guest'");
        }

        // Load location data.
        if (!empty($users)) {
            $this->load_location_data();
        }

        // Get coordinates for each user.
        $locations = [];
        foreach ($users as $user) {
            if ($userlocation = $this->build_pin($user)) {
                $locations[$user->id] = $userlocation;
            }
        }

        // Write pins info.
        $pins = '<script type="application/json" id="map-pins-data">';
        $pins .= json_encode(array_values($locations), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $pins .= '</script>';

        // Add leaflet CSS and JS.
        $html = '<div id="worldmap" style="height: 600px;"></div>';
        $leafletcss = html_writer::empty_tag('link', [
            'rel' => 'stylesheet',
            'href' => (string) new moodle_url('/filter/mapofusers/vendor/leaflet/leaflet.css')
        ]);
        $leafletjs = html_writer::script('', (string) new moodle_url('/filter/mapofusers/vendor/leaflet/leaflet.js'));

        // Add leaflet map configuration.
        $configraw = $this->config->map_config;
        $configdecoded = json_decode($configraw);
        if ($configdecoded !== null) {
            $PAGE->requires->js_init_code(
                'window.mapofusersConfig = ' . json_encode($configdecoded, JSON_UNESCAPED_SLASHES) . ';'
            );
        } else {
            debugging('Invalid JSON in map_config');
        }

        // Add leaflet map initialization script.
        $mapinitjs = html_writer::script('', (string) new moodle_url('/filter/mapofusers/js/map_init.js'));

        return $leafletcss . $html . $leafletjs . $pins . $mapinitjs;
    }

    /**
     * Load location data.
     */
    protected function load_location_data() {

        // Use cache to avoid reloading data.
        $cache = \cache::make('filter_mapofusers', 'locationdata');
        $locationdata = $cache->get('parsed');

        // Load CSV into cache.
        if (!$locationdata) {
            $csv = file_get_contents(__DIR__ . '/../vendor/simplemaps/worldcities.csv');
            $lines = explode("\n", $csv);
            $locationdata = [];
            foreach ($lines as $line) {
                $data = str_getcsv($line);
                if (count($data) >= 3) {
                    $locationdata[$data[5]][$data[0]] = [
                        'lat' => (float)$data[2],
                        'lng' => (float)$data[3],
                        'country' => $data[4],
                    ];
                }
            }

            $cache->set('parsed', $locationdata);
        }

        $this->locationdata = $locationdata;
    }

    /**
     * Build pin data for a user.
     *
     * @param stdClass $user
     * @return array
     */
    protected function build_pin($user) {

        // Load profile fields.
        profile_load_custom_fields($user);

        // Get country and city from user profile fields.
        $country = $user->country;
        $city = $user->city;

        // Location string for pin.
        $location = '';
        if ($country = $user->country) {
            $location = $country;
            if ($city = $user->city) {
                $location = $city . ', ' . $country;
            }
        }

        // Get country.
        $coordinates = [];
        if (array_key_exists($country, $this->locationdata)) {

            $countrydata = $this->locationdata[$country];

            // Get city.
            if (array_key_exists($city, $countrydata)) {
                $coordinates = $countrydata[$city];
            } else {
                // If city is not found, return the first location in the country.
                $coordinates = reset($countrydata);
            }
        }

        // If coordinates are found, merge with location.
        if($coordinates) {

            // Get user location.
            if ($city) {
                $user->location = "$city, {$coordinates['country']}";
            } else {
                $user->location = $coordinates['country'];
            }

            // Get userlink.
            $name = fullname($user);
            $userurl = new moodle_url('/user/profile.php', ['id' => $user->id]);
            $user->userlink = html_writer::link($userurl, $name, ['class' => 'mapofusers-pin-name']);

            // Re-write user profile fields for token replacement.
            if ($user->profile) {
                foreach ($user->profile as $key => $value) {
                    $user->{'profile_' . $key} = $value;
                }
            }

            // Build label, replacing tokens.
            $label = $this->config->labeltext;
            foreach ($user as $key => $value) {
                if (strpos($label, '{' . $key . '}') !== false) {
                    $label = str_replace('{' . $key . '}', $value, $label);
                }
            }

            // Build pin data.
            $pin = [];
            $pin['name'] = $name;
            $pin['label'] = $label;
            $pin['location'] = $user->location;
            if (!empty($this->config->pin_image)) {
                $pin['image'] = $this->config->pin_image;
            }

            return array_merge($pin, $coordinates);
        }
    }

    /**
     * Returns original text plus error message.
     *
     * @param string $errormessage
     * @param string $text
     * @return string
     */
    protected function return_error($errormsg, $text) {
        return '<div class="alert alert-danger">' . $errormsg . '</div>' . $text;
    }

}