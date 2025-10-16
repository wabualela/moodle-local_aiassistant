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

namespace local_aiassistant;

use html_writer;
use moodle_url;

/**
 * Class hook_callbacks
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks
{

    /**
     * Add AI assistant FAB and chat interface after the main region content.
     *
     * @param \core\hook\output\after_standard_main_region_html_generation $hook
     */
    public static function add_fab(
        \core\hook\output\after_standard_main_region_html_generation $hook,
        ): void {
        global $OUTPUT, $PAGE;

        // Check if the assistant is enabled.
        if (!get_config('local_aiassistant', 'enable')) {
            return;
        }

        // Don't show on login page or during installation.
        if ($PAGE->pagelayout === 'login' || during_initial_install()) {
            return;
        }

        // Initialize the JavaScript module.
        $PAGE->requires->js_call_amd('local_aiassistant/chat', 'init');

        // Get configurable settings.
        $assistantname = get_config('local_aiassistant', 'assistantname');
        if (empty($assistantname)) {
            $assistantname = get_string('assistant_name', 'local_aiassistant');
        }

        $fabcolor = get_config('local_aiassistant', 'fabcolor');
        if (empty($fabcolor)) {
            $fabcolor = '#0f6cbf';
        }

        $welcomemessage = get_config('local_aiassistant', 'welcomemessage');
        if (empty($welcomemessage)) {
            $welcomemessage = get_string('welcome_message', 'local_aiassistant');
        }

        // Get custom FAB icon if uploaded.
        $fs = get_file_storage();
        $syscontext = \context_system::instance();
        $files = $fs->get_area_files($syscontext->id, 'local_aiassistant', 'fabicon', 0, 'itemid', false);
        $iconurl = '';
        if (!empty($files)) {
            $file = reset($files);
            $iconurl = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename()
            )->out();
        } else {
            // Use default icon.
            $iconurl = (new moodle_url('/local/aiassistant/pix/sheikh.svg'))->out(false);
        }

        // Settings URL for the options button.
        $settingsurl = (new moodle_url('/admin/settings.php', ['section' => 'local_aiassistant']))->out(false);

        // Prepare context for templates.
        $context = [
            'iconurl' => $iconurl,
            'fabcolor' => $fabcolor,
            'assistantname' => $assistantname,
            'welcomemessage' => $welcomemessage,
            'settingsurl' => $settingsurl,
            'current_time' => userdate(time(), get_string('strftimetime', 'core_langconfig')),
            'error_generic' => get_string('error_generic', 'local_aiassistant'),
        ];

        // Add the FAB button.
        $hook->add_html(
            $OUTPUT->render_from_template('local_aiassistant/fab', $context)
        );

        // Add the chat interface (hidden by default).
        $hook->add_html(
            $OUTPUT->render_from_template('local_aiassistant/chat', $context)
        );
    }
}
