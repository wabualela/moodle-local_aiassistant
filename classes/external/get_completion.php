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
use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use external_multiple_structure;

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
        global $PAGE;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'message' => $message,
            'history' => $history,
        ]);

        // Check capability.
        $context = context_system::instance();
        self::validate_context($context);

        // Check if assistant is enabled.
        if (!get_config('local_aiassistant', 'enable')) {
            return [
                'success' => false,
                'message' => get_string('assistantdisabled', 'local_aiassistant'),
                'formattedmessage' => '',
            ];
        }

        // Create completion.
        $completion = new \local_aiassistant\completion\chat($params['message'], $params['history']);
        $response = $completion->create_completion();

        // Format the message as Markdown if successful.
        $formattedmessage = '';
        if ($response['success']) {
            $formattedmessage = format_text($response['message'], FORMAT_MARKDOWN, ['context' => $context]);
        }

        return [
            'success' => $response['success'],
            'message' => $response['message'],
            'formattedmessage' => $formattedmessage,
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
        ]);
    }
}
