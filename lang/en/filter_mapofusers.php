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
 * Strings for component 'filter_mapofusers', language 'en'.
 *
 * @package    filter_mapofusers
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['filtername'] = 'Map of Users';
$string['privacy:metadata'] = 'The Map of Users filter plugin does not store any personal data.';
$string['errormsg'] = 'Malformed parameters for filter_mapofusers. Please check your syntax.';
$string['labeltext'] = 'HTML for pin labels';
$string['labeltext_desc'] = 'This HTML will be used as a label for the pins on the map. <ul>
<li>You can use placeholders like {firstname} and {lastname} to include any value from the user</li>
<li>Use {profile_[customfieldname]} for custom profile fields</li>
<li>Use {location} for the location of the user</li>
<li>Use {userlink} for the fullname of the user, linking to their profile</li></ul>
';
$string['mapconfig'] = 'Leaflet map configuration';
$string['mapconfig_desc'] = 'This configuration will be used to set up the leaflet.js map. Must be valid JSON. See leaflet.js or the ai or your choice for more info. Leave on default if unsure.';
$string['pinimage'] = 'Pin image';
$string['pinimage_desc'] = 'Optionally upload an alternative image to use as a pin on the map. The image should be small, ideally 25x41 pixels.';