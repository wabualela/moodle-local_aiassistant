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
 * Custom admin setting for OpenAI Assistant ID with validation
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiassistant;

use html_writer;

defined('MOODLE_INTERNAL') || die();

/**
 * Admin setting for assistant ID that fetches and displays assistant info
 */
class admin_setting_assistant_id extends \admin_setting {

    /**
     * Constructor
     *
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param mixed $defaultsetting
     */
    public function __construct($name, $visiblename, $description, $defaultsetting) {
        parent::__construct($name, $visiblename, $description, $defaultsetting);
    }

    /**
     * Return the setting
     *
     * @return mixed returns config if successful else null
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Store the setting
     *
     * @param string $data
     * @return string empty string if ok, string error message otherwise
     */
    public function write_setting($data) {
        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }
        return ($this->config_write($this->name, $data) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Validate the assistant ID
     *
     * @param string $data
     * @return mixed true if ok, string error message otherwise
     */
    public function validate($data) {
        // Allow empty value.
        if (trim($data) === '') {
            return true;
        }

        // Check format (should start with asst_).
        if (!preg_match('/^asst_[a-zA-Z0-9]+$/', $data)) {
            return get_string('assistantid_invalid_format', 'local_aiassistant');
        }

        return true;
    }

    /**
     * Return an XHTML string for the setting
     *
     * @param string $data
     * @param string $query
     * @return string Returns an XHTML string
     */
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

        $default = $this->get_defaultsetting();

        // Create select dropdown with loading message.
        $select = html_writer::select(
            ['' => get_string('loading', 'local_aiassistant')],
            $this->get_full_name(),
            $data,
            false,
            ['id' => $this->get_id(), 'class' => 'form-control', 'data-current-value' => $data]
        );

        // Add container for assistant info.
        $assistantinfo = html_writer::div('', 'alert alert-info', [
            'id' => 'assistant-info-container',
            'style' => 'display: none; margin-top: 10px;'
        ]);

        // Add loading indicator.
        $loading = html_writer::div(
            html_writer::tag('i', '', ['class' => 'fa fa-spinner fa-spin']) . ' ' .
            get_string('fetching_assistants', 'local_aiassistant'),
            'alert alert-secondary',
            [
                'id' => 'assistant-loading',
                'style' => 'margin-top: 10px;'
            ]
        );

        // Initialize JavaScript.
        $PAGE->requires->js_call_amd('local_aiassistant/admin_assistant', 'init', [
            $this->get_id()
        ]);

        return format_admin_setting($this, $this->visiblename, $select . $loading . $assistantinfo,
            $this->description, true, '', $default, $query);
    }
}
