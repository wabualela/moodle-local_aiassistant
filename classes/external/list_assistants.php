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
 * External function to list all assistants from OpenAI
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
 * External function list_assistants
 */
class list_assistants extends external_api
{

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function execute_parameters()
    {
        return new external_function_parameters([]);
    }

    /**
     * List all assistants from OpenAI
     *
     * @return array Response data
     */
    public static function execute() {
        // Require admin capability.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        try {
            // Get API key from AI provider.
            $apikey = \local_aiassistant\completion\openai_client::get_api_key();

            // Create OpenAI client.
            $client = new \local_aiassistant\completion\openai_client($apikey);

            // Fetch assistants list from OpenAI.
            $response = $client->list_assistants('desc', 100);

            $assistants = [];
            if (isset($response->data) && is_array($response->data)) {
                foreach ($response->data as $assistant) {
                    $assistants[] = [
                        'id' => $assistant->id ?? '',
                        'name' => $assistant->name ?? 'Unnamed Assistant',
                        'description' => $assistant->description ?? '',
                        'model' => $assistant->model ?? '',
                        'created_at' => $assistant->created_at ?? 0,
                    ];
                }
            }

            return [
                'success' => true,
                'assistants' => $assistants,
                'error' => '',
            ];

        } catch (\moodle_exception $e) {
            debugging('Error fetching assistants: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'assistants' => [],
            ];
        } catch (\Exception $e) {
            debugging('Exception fetching assistants: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'error' => 'Error: ' . $e->getMessage(),
                'assistants' => [],
            ];
        }
    }

    /**
     * Returns description of method result value
     *
     * @return external_single_structure
     */
    public static function execute_returns()
    {
        return new external_single_structure([
            'success'    => new external_value(PARAM_BOOL, 'Whether the request was successful'),
            'assistants' => new external_multiple_structure(
                new external_single_structure([
                    'id'          => new external_value(PARAM_TEXT, 'Assistant ID'),
                    'name'        => new external_value(PARAM_TEXT, 'Assistant name'),
                    'description' => new external_value(PARAM_TEXT, 'Assistant description'),
                    'model'       => new external_value(PARAM_TEXT, 'Model'),
                    'created_at'  => new external_value(PARAM_INT, 'Creation timestamp'),
                ])
            ),
            'error'      => new external_value(PARAM_TEXT, 'Error message if failed', VALUE_OPTIONAL),
        ]);
    }
}
