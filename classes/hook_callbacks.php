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

        // Prepare context for templates.
        $context = [
            'iconurl' => (new moodle_url('/local/aiassistant/pix/sheikh.svg'))->out(false),
            'current_time' => userdate(time(), get_string('strftimetime', 'core_langconfig')),
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
