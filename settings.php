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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/renderer.php');

if ($hassiteconfig) {
    if ($ADMIN->fulltree) {

        // Labels
        $settings->add(new admin_setting_configtextarea(
            'filter_mapofusers/labeltext',
            get_string('labeltext', 'filter_mapofusers'),
            get_string('labeltext_desc', 'filter_mapofusers'),
            '{userlink} - {location}',
            PARAM_TEXT
        ));

        // Map configuration
        $settings->add(new admin_setting_configtextarea(
            'filter_mapofusers/map_config',
            get_string('mapconfig', 'filter_mapofusers'),
            get_string('mapconfig_desc', 'filter_mapofusers'),
            '{
                "center": [20, 0],
                "zoom": 2,
                "minZoom": 2,
                "maxZoom": 10,
                "scrollWheelZoom": true,
                "maxBoundsViscosity": 1.0,
                "maxBounds": [
                    [-85, -180],
                    [85, 180]
                ]
            }',
            PARAM_RAW
        ));

        // Pin image.
        $settings->add(new admin_setting_configtext(
            'filter_mapofusers/pin_image',
            get_string('pinimage', 'filter_mapofusers'),
            get_string('pinimage_desc', 'filter_mapofusers'),
            '',
            PARAM_URL
        ));
    }
}


