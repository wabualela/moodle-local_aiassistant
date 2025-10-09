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
    public function create_completion() {
        if (empty($this->apikey)) {
            return [
                'success' => false,
                'message' => get_string('apikeynotset', 'local_aiassistant'),
            ];
        }

        // Format conversation history.
        $messages = $this->format_history();

        // Add system prompt at the beginning.
        array_unshift($messages, ['role' => 'system', 'content' => $this->prompt]);

        // Add current user message.
        $messages[] = ['role' => 'user', 'content' => $this->message];

        // Make API call.
        $response = $this->make_api_call($messages);

        return $response;
    }

    /**
     * Format conversation history for API
     *
     * @return array Formatted messages
     */
    protected function format_history() {
        $messages = [];

        foreach ($this->history as $index => $item) {
            // Alternate between user and assistant based on index.
            $role = $index % 2 === 0 ? 'user' : 'assistant';
            $messages[] = [
                'role' => $role,
                'content' => $item['message'],
            ];
        }

        return $messages;
    }

    /**
     * Make API call to OpenAI
     *
     * @param array $messages Formatted messages
     * @return array Response data
     */
    protected function make_api_call($messages) {
        $curlbody = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => $this->maxlength,
            'top_p' => $this->topp,
            'frequency_penalty' => $this->frequency,
            'presence_penalty' => $this->presence,
        ];

        $curl = new \curl();
        $curl->setopt([
            'CURLOPT_HTTPHEADER' => [
                'Authorization: Bearer ' . $this->apikey,
                'Content-Type: application/json',
            ],
        ]);

        try {
            $response = $curl->post('https://api.openai.com/v1/chat/completions', json_encode($curlbody));
            $responsedata = json_decode($response);

            if (property_exists($responsedata, 'error')) {
                return [
                    'success' => false,
                    'message' => $responsedata->error->message,
                ];
            }

            if (!property_exists($responsedata, 'choices') || empty($responsedata->choices)) {
                return [
                    'success' => false,
                    'message' => get_string('invalidresponse', 'local_aiassistant'),
                ];
            }

            return [
                'success' => true,
                'message' => $responsedata->choices[0]->message->content,
                'id' => $responsedata->id ?? '',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => get_string('apierror', 'local_aiassistant') . ': ' . $e->getMessage(),
            ];
        }
    }
}
