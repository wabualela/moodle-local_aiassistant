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

    // General settings heading.
    $settings->add(new admin_setting_heading(
        'local_aiassistant/generalheading',
        get_string('generalheading', 'local_aiassistant'),
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_aiassistant/enable',
        get_string('enable', 'local_aiassistant'),
        get_string('enable_desc', 'local_aiassistant'),
        1
    ));

    // Appearance settings heading.
    $settings->add(new admin_setting_heading(
        'local_aiassistant/appearanceheading',
        get_string('appearanceheading', 'local_aiassistant'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_aiassistant/assistantname',
        get_string('assistantname', 'local_aiassistant'),
        get_string('assistantname_desc', 'local_aiassistant'),
        get_string('assistant_name', 'local_aiassistant')
    ));

    $settings->add(new admin_setting_configcolourpicker(
        'local_aiassistant/fabcolor',
        get_string('fabcolor', 'local_aiassistant'),
        get_string('fabcolor_desc', 'local_aiassistant'),
        '#0f6cbf'
    ));

    $settings->add(new admin_setting_configstoredfile(
        'local_aiassistant/fabicon',
        get_string('fabicon', 'local_aiassistant'),
        get_string('fabicon_desc', 'local_aiassistant'),
        'fabicon',
        0,
        ['maxfiles' => 1, 'accepted_types' => ['.png', '.jpg', '.svg']]
    ));

    $settings->add(new admin_setting_confightmleditor(
        'local_aiassistant/welcomemessage',
        get_string('welcomemessage', 'local_aiassistant'),
        get_string('welcomemessage_desc', 'local_aiassistant'),
        get_string('welcome_message', 'local_aiassistant')
    ));

    // AI integration heading.
    $settings->add(new admin_setting_heading(
        'local_aiassistant/integrationheading',
        get_string('integrationheading', 'local_aiassistant'),
        get_string('integrationheading_desc', 'local_aiassistant')
    ));

    // API mode selection.
    $settings->add(new admin_setting_configselect(
        'local_aiassistant/apimode',
        get_string('apimode', 'local_aiassistant'),
        get_string('apimode_desc', 'local_aiassistant'),
        'completion',
        [
            'completion' => get_string('apimode_completion', 'local_aiassistant'),
            'assistant' => get_string('apimode_assistant', 'local_aiassistant'),
        ]
    ));

    // Assistant ID (for Assistants API) - with assistants dropdown.
    $settings->add(new \local_aiassistant\admin_setting_assistant_id(
        'local_aiassistant/assistantid',
        get_string('assistantid', 'local_aiassistant'),
        get_string('assistantid_desc', 'local_aiassistant'),
        ''
    ));

    // System prompt (for Completion API).
    $settings->add(new admin_setting_configtextarea(
        'local_aiassistant/prompt',
        get_string('prompt', 'local_aiassistant'),
        get_string('prompt_desc', 'local_aiassistant'),
        get_string('defaultprompt', 'local_aiassistant')
    ));

    $ADMIN->add('localplugins', $settings);
}
