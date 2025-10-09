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
 * Chat completion class for OpenAI Chat API
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiassistant\completion;

defined('MOODLE_INTERNAL') || die();

/**
 * Chat completion class
 */
class chat extends completion {

    /**
     * Create completion using OpenAI Chat API
     *
     * @return array Response data
     */
    public function create_completion(): array {
        $prompttext = $this->build_prompttext();

        $action = new \core_ai\aiactions\generate_text(
            contextid: $this->get_contextid(),
            userid: $this->get_userid(),
            prompttext: $prompttext,
        );

        try {
            $manager = \core\di::get(\core_ai\manager::class);
            $response = $manager->process_action($action);
        } catch (\Throwable $exception) {
            debugging($exception->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => get_string('aiintegrationerror', 'local_aiassistant'),
                'errorcode' => 0,
            ];
        }

        if (!$response->get_success()) {
            $errormessage = $response->get_errormessage() ?: get_string('aiintegrationerror', 'local_aiassistant');
            return [
                'success' => false,
                'message' => $errormessage,
                'errorcode' => $response->get_errorcode(),
            ];
        }

        $data = $response->get_response_data();

        return [
            'success' => true,
            'message' => $data['generatedcontent'] ?? '',
            'id' => $data['id'] ?? '',
        ];
    }
}
