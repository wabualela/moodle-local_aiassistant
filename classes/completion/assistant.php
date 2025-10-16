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
 * Assistant completion class for OpenAI Assistants API
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiassistant\completion;

defined('MOODLE_INTERNAL') || die();

/**
 * Assistant completion class using OpenAI Assistants API
 */
class assistant extends completion {

    /** @var string OpenAI API endpoint */
    private const API_BASE = 'https://api.openai.com/v1';

    /** @var int Maximum polling attempts */
    private const MAX_POLL_ATTEMPTS = 30;

    /** @var int Polling interval in milliseconds */
    private const POLL_INTERVAL = 1000;

    /**
     * Create completion using OpenAI Assistants API
     *
     * @return array Response data
     */
    public function create_completion(): array {
        global $DB;

        // Get assistant ID from config.
        $assistantid = get_config('local_aiassistant', 'assistantid');
        if (empty($assistantid)) {
            return [
                'success' => false,
                'message' => get_string('assistantid_required', 'local_aiassistant'),
                'errorcode' => 0,
            ];
        }

        // Get API key from Moodle's AI subsystem.
        $apikey = $this->get_api_key();
        if (!$apikey) {
            debugging('No API key found for OpenAI', DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => 'API key not configured. Please configure OpenAI API key in Site administration > AI > AI Providers.',
                'errorcode' => 0,
            ];
        }

        try {
            debugging('Starting assistant completion for user ' . $this->get_userid(), DEBUG_DEVELOPER);

            // Get or create thread for this user.
            debugging('Getting or creating thread...', DEBUG_DEVELOPER);
            $threadid = $this->get_or_create_thread($apikey);
            debugging('Thread ID: ' . $threadid, DEBUG_DEVELOPER);

            // Add message to thread.
            debugging('Adding message to thread...', DEBUG_DEVELOPER);
            $this->add_message_to_thread($threadid, $this->message, $apikey);

            // Run the assistant.
            debugging('Running assistant with ID: ' . $assistantid, DEBUG_DEVELOPER);
            $runid = $this->run_assistant($threadid, $assistantid, $apikey);
            debugging('Run ID: ' . $runid, DEBUG_DEVELOPER);

            // Poll for completion.
            debugging('Polling for completion...', DEBUG_DEVELOPER);
            $run = $this->poll_run($threadid, $runid, $apikey);

            if ($run['status'] !== 'completed') {
                debugging("Assistant run failed with status: {$run['status']}", DEBUG_DEVELOPER);
                return [
                    'success' => false,
                    'message' => get_string('aiintegrationerror', 'local_aiassistant'),
                    'errorcode' => 0,
                ];
            }

            // Retrieve the assistant's response.
            $messages = $this->get_thread_messages($threadid, $apikey);
            $response = $this->extract_assistant_response($messages);

            return [
                'success' => true,
                'message' => $response,
            ];

        } catch (\Exception $e) {
            debugging('Assistant API Error: ' . $e->getMessage(), DEBUG_DEVELOPER);
            debugging('Stack trace: ' . $e->getTraceAsString(), DEBUG_DEVELOPER);
            return [
                'success' => false,
                'message' => 'Assistant API Error: ' . $e->getMessage(),
                'errorcode' => 0,
            ];
        }
    }

    /**
     * Get API key from Moodle's AI subsystem
     *
     * @return string|null API key or null if not found
     */
    private function get_api_key(): ?string {
        try {
            return openai_client::get_api_key();
        } catch (\Exception $e) {
            debugging('Error getting API key from AI manager: ' . $e->getMessage(), DEBUG_DEVELOPER);
            return null;
        }
    }

    /**
     * Get or create a thread for the current user
     *
     * @param string $apikey OpenAI API key
     * @return string Thread ID
     */
    private function get_or_create_thread(string $apikey): string {
        global $DB;

        $userid = $this->get_userid();

        // Try to get existing thread.
        $record = $DB->get_record('local_aiassistant_threads', ['userid' => $userid]);

        if ($record && !empty($record->threadid)) {
            return $record->threadid;
        }

        // Create new thread.
        $response = $this->make_api_request('POST', '/threads', [], $apikey);

        if (empty($response['id'])) {
            throw new \moodle_exception('Failed to create thread');
        }

        $threadid = $response['id'];

        // Store thread ID.
        $data = new \stdClass();
        $data->userid = $userid;
        $data->threadid = $threadid;
        $data->timecreated = time();
        $data->timemodified = time();

        if ($record) {
            $data->id = $record->id;
            $DB->update_record('local_aiassistant_threads', $data);
        } else {
            $DB->insert_record('local_aiassistant_threads', $data);
        }

        return $threadid;
    }

    /**
     * Add a message to a thread
     *
     * @param string $threadid Thread ID
     * @param string $message Message content
     * @param string $apikey API key
     * @return void
     */
    private function add_message_to_thread(string $threadid, string $message, string $apikey): void {
        $data = [
            'role' => 'user',
            'content' => $message,
        ];

        $this->make_api_request('POST', "/threads/{$threadid}/messages", $data, $apikey);
    }

    /**
     * Run the assistant on a thread
     *
     * @param string $threadid Thread ID
     * @param string $assistantid Assistant ID
     * @param string $apikey API key
     * @return string Run ID
     */
    private function run_assistant(string $threadid, string $assistantid, string $apikey): string {
        $data = [
            'assistant_id' => $assistantid,
        ];

        $response = $this->make_api_request('POST', "/threads/{$threadid}/runs", $data, $apikey);

        if (empty($response['id'])) {
            throw new \moodle_exception('Failed to start assistant run');
        }

        return $response['id'];
    }

    /**
     * Poll for run completion
     *
     * @param string $threadid Thread ID
     * @param string $runid Run ID
     * @param string $apikey API key
     * @return array Run status
     */
    private function poll_run(string $threadid, string $runid, string $apikey): array {
        $attempts = 0;

        while ($attempts < self::MAX_POLL_ATTEMPTS) {
            $run = $this->make_api_request('GET', "/threads/{$threadid}/runs/{$runid}", [], $apikey);

            $status = $run['status'] ?? 'unknown';

            if (in_array($status, ['completed', 'failed', 'cancelled', 'expired'])) {
                return $run;
            }

            // Wait before next poll.
            usleep(self::POLL_INTERVAL * 1000);
            $attempts++;
        }

        throw new \moodle_exception('Assistant run timed out');
    }

    /**
     * Get messages from a thread
     *
     * @param string $threadid Thread ID
     * @param string $apikey API key
     * @return array Messages
     */
    private function get_thread_messages(string $threadid, string $apikey): array {
        $response = $this->make_api_request('GET', "/threads/{$threadid}/messages?order=desc&limit=1", [], $apikey);
        return $response['data'] ?? [];
    }

    /**
     * Extract assistant response from messages
     *
     * @param array $messages Thread messages
     * @return string Assistant response
     */
    private function extract_assistant_response(array $messages): string {
        foreach ($messages as $message) {
            if (($message['role'] ?? '') === 'assistant') {
                $content = $message['content'] ?? [];
                foreach ($content as $item) {
                    if (($item['type'] ?? '') === 'text') {
                        return $item['text']['value'] ?? '';
                    }
                }
            }
        }

        return '';
    }

    /**
     * Make an API request to OpenAI
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $data Request data
     * @param string $apikey API key
     * @return array Response data
     */
    private function make_api_request(string $method, string $endpoint, array $data, string $apikey): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $url = self::API_BASE . $endpoint;

        $headers = [
            'Authorization: Bearer ' . $apikey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
        ];

        $curl = new \curl();
        $curl->setHeader($headers);

        if ($method === 'POST') {
            $response = $curl->post($url, json_encode($data));
        } else if ($method === 'GET') {
            $response = $curl->get($url);
        } else {
            throw new \moodle_exception("Unsupported HTTP method: {$method}");
        }

        // Check for curl errors.
        if ($curl->get_errno()) {
            debugging("cURL error: {$curl->error}", DEBUG_DEVELOPER);
            throw new \moodle_exception("cURL error: {$curl->error}");
        }

        // Get HTTP status code.
        $info = $curl->get_info();
        $httpcode = isset($info['http_code']) ? $info['http_code'] : 0;

        if ($httpcode !== 200) {
            debugging("API request to {$url} failed with status {$httpcode}: {$response}", DEBUG_DEVELOPER);
            $errordata = json_decode($response, true);
            $errormsg = $errordata['error']['message'] ?? 'Unknown error';
            throw new \moodle_exception("OpenAI API error ({$httpcode}): {$errormsg}");
        }

        return json_decode($response, true) ?: [];
    }
}
