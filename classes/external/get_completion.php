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
 * External function to get AI completion
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiassistant\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function get_completion
 */
class get_completion extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'message' => new external_value(PARAM_TEXT, 'User message'),
            'history' => new external_multiple_structure(
                new external_single_structure([
                    'message' => new external_value(PARAM_TEXT, 'Message content'),
                ]),
                'Conversation history',
                VALUE_DEFAULT,
                []
            ),
        ]);
    }

    /**
     * Get completion from AI
     *
     * @param string $message User message
     * @param array $history Conversation history
     * @return array Response data
     */
    public static function execute($message, $history) {
        global $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'message' => $message,
            'history' => $history,
        ]);

        // Require user session to enforce capability checks.
        require_login();

        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/aiassistant:view', $context);

        // Check if assistant is enabled.
        if (!get_config('local_aiassistant', 'enable')) {
            return [
                'success' => false,
                'message' => get_string('assistantdisabled', 'local_aiassistant'),
                'formattedmessage' => '',
                'errorcode' => 0,
            ];
        }

        // Get API mode from config.
        $apimode = get_config('local_aiassistant', 'apimode') ?: 'completion';

        // Create completion based on API mode.
        if ($apimode === 'assistant') {
            $completion = new \local_aiassistant\completion\assistant(
                message: $params['message'],
                history: $params['history'],
                context: $context,
                userid: $USER->id,
                username: fullname($USER, true)
            );
        } else {
            $completion = new \local_aiassistant\completion\chat(
                message: $params['message'],
                history: $params['history'],
                context: $context,
                userid: $USER->id,
                username: fullname($USER, true)
            );
        }

        $response = $completion->create_completion();

        // Format the message as Markdown if successful.
        $formattedmessage = '';
        if ($response['success']) {
            $formattedmessage = format_text(
                $response['message'],
                FORMAT_MARKDOWN,
                [
                    'context' => $context,
                    'noclean' => false,
                    'filter' => false,
                ]
            );
        }

        return [
            'success' => $response['success'],
            'message' => $response['message'],
            'formattedmessage' => $formattedmessage,
            'errorcode' => $response['errorcode'] ?? 0,
        ];
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
            'message' => new external_value(PARAM_RAW, 'AI response message'),
            'formattedmessage' => new external_value(PARAM_RAW, 'Formatted HTML message'),
            'errorcode' => new external_value(PARAM_INT, 'AI subsystem error code', VALUE_DEFAULT, 0),
        ]);
    }
}
