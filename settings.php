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
 * Settings page.
 *
 * @package   local_aiassistant
 * @copyright 2025, Wail Abualela wailabualela@alborhan.sa
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_aiassistant', get_string('pluginname', 'local_aiassistant'));

    $settings->add(new admin_setting_configcheckbox(
        'local_aiassistant/enable',
        get_string('enable', 'local_aiassistant'),
        get_string('enable_desc', 'local_aiassistant'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_aiassistant/apikey',
        get_string('apikey', 'local_aiassistant'),
        get_string('apikey_desc', 'local_aiassistant'),
        ''
    ));

    $ADMIN->add('localplugins', $settings);
}
