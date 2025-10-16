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
 * External function to get assistant info from OpenAI
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiassistant\external;

use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External function get_assistant_info
 */
class get_assistant_info extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'assistantid' => new external_value(PARAM_TEXT, 'Assistant ID'),
        ]);
    }

    /**
     * Get assistant info from OpenAI
     *
     * @param string $assistantid Assistant ID
     * @return array Response data
     */
    public static function execute($assistantid) {
        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), [
            'assistantid' => $assistantid,
        ]);

        // Require admin capability.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        try {
            // Get API key from AI provider.
            $apikey = \local_aiassistant\completion\openai_client::get_api_key();

            // Create OpenAI client.
            $client = new \local_aiassistant\completion\openai_client($apikey);

            // Fetch assistant info from OpenAI.
            $data = $client->get_assistant($params['assistantid']);

            return [
                'success' => true,
                'name' => $data->name ?? '',
                'description' => $data->description ?? '',
                'model' => $data->model ?? '',
                'instructions' => $data->instructions ?? '',
                'tools' => !empty($data->tools) ? count($data->tools) : 0,
                'error' => '',
            ];

        } catch (\moodle_exception $e) {
            debugging('Error fetching assistant info: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'name' => '',
                'description' => '',
                'model' => '',
                'instructions' => '',
                'tools' => 0,
            ];
        } catch (\Exception $e) {
            debugging('Exception fetching assistant info: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'name' => '',
                'description' => '',
                'model' => '',
                'instructions' => '',
                'tools' => 0,
            ];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'success' => new external_value(PARAM_BOOL, 'Whether the request was successful'),
            'name' => new external_value(PARAM_TEXT, 'Assistant name', VALUE_OPTIONAL),
            'description' => new external_value(PARAM_TEXT, 'Assistant description', VALUE_OPTIONAL),
            'model' => new external_value(PARAM_TEXT, 'Model', VALUE_OPTIONAL),
            'instructions' => new external_value(PARAM_TEXT, 'Instructions', VALUE_OPTIONAL),
            'tools' => new external_value(PARAM_INT, 'Number of tools', VALUE_OPTIONAL),
            'error' => new external_value(PARAM_TEXT, 'Error message if failed', VALUE_OPTIONAL),
        ]);
    }
}
