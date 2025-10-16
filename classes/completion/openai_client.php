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
 * OpenAI API client helper class
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiassistant\completion;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/filelib.php');

/**
 * OpenAI API client helper class
 */
class openai_client {

    /** @var string OpenAI API base URL */
    const API_BASE_URL = 'https://api.openai.com/v1';

    /** @var string API key */
    private $apikey;

    /**
     * Constructor
     *
     * @param string $apikey OpenAI API key
     */
    public function __construct($apikey) {
        $this->apikey = $apikey;
    }

    /**
     * Get API key from AI provider configuration
     *
     * @return string|null API key or null if not configured
     * @throws \moodle_exception If provider is not configured
     */
    public static function get_api_key() {
        try {
            $manager = \core\di::get(\core_ai\manager::class);
            $providers = $manager->get_provider_instances();

            if (empty($providers)) {
                throw new \moodle_exception(
                    'noproviders',
                    'local_aiassistant',
                    '',
                    null,
                    'No AI providers configured. Please configure OpenAI in Site administration > AI > AI Providers.'
                );
            }

            // Get first provider.
            $provider = reset($providers);

            // Get API key from provider configuration.
            if (!isset($provider->config['apikey']) || empty($provider->config['apikey'])) {
                throw new \moodle_exception(
                    'noapikey',
                    'local_aiassistant',
                    '',
                    null,
                    'OpenAI API key not configured. Please set it in Site administration > AI > AI Providers.'
                );
            }

            return $provider->config['apikey'];

        } catch (\Exception $e) {
            throw new \moodle_exception(
                'providererror',
                'local_aiassistant',
                '',
                null,
                'Could not access OpenAI provider: ' . $e->getMessage()
            );
        }
    }

    /**
     * Make a GET request to OpenAI API
     *
     * @param string $endpoint API endpoint (e.g., '/assistants')
     * @param array $params Query parameters
     * @return object Decoded JSON response
     * @throws \moodle_exception If request fails
     */
    public function get($endpoint, array $params = []) {
        return $this->request('GET', $endpoint, $params);
    }

    /**
     * Make a POST request to OpenAI API
     *
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return object Decoded JSON response
     * @throws \moodle_exception If request fails
     */
    public function post($endpoint, array $data = []) {
        return $this->request('POST', $endpoint, [], $data);
    }

    /**
     * Make a request to OpenAI API
     *
     * @param string $method HTTP method (GET, POST, etc.)
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param array $data Request body data
     * @return object Decoded JSON response
     * @throws \moodle_exception If request fails
     */
    private function request($method, $endpoint, array $params = [], array $data = []) {
        // Build URL.
        $url = self::API_BASE_URL . $endpoint;
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        // Prepare curl instance.
        $curl = new \curl();

        // Set headers.
        $headers = [
            'Authorization: Bearer ' . $this->apikey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
        ];

        $curl->setHeader($headers);

        // Make request.
        if ($method === 'GET') {
            $response = $curl->get($url);
        } else if ($method === 'POST') {
            $response = $curl->post($url, json_encode($data));
        } else {
            throw new \moodle_exception('invalidmethod', 'local_aiassistant', '', null, "Unsupported HTTP method: {$method}");
        }

        // Check for curl errors.
        if ($curl->get_errno()) {
            debugging('cURL error ' . $curl->get_errno() . ': ' . $curl->error, DEBUG_DEVELOPER);
            throw new \moodle_exception(
                'curlerror',
                'local_aiassistant',
                '',
                null,
                'cURL error: ' . $curl->error
            );
        }

        // Get HTTP status code.
        $info = $curl->get_info();
        $httpcode = isset($info['http_code']) ? $info['http_code'] : 0;

        // Debug logging.
        debugging("OpenAI API Response - HTTP {$httpcode} - URL: {$url}", DEBUG_DEVELOPER);
        debugging("Response length: " . strlen($response) . " bytes", DEBUG_DEVELOPER);

        // Decode response.
        $decoded = json_decode($response);

        if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
            debugging('JSON decode error: ' . json_last_error_msg() . ' - Response: ' . substr($response, 0, 500), DEBUG_DEVELOPER);
            throw new \moodle_exception(
                'jsonerror',
                'local_aiassistant',
                '',
                null,
                'Failed to decode JSON response: ' . json_last_error_msg() . ' (HTTP ' . $httpcode . ')'
            );
        }

        // Check HTTP status code.
        if ($httpcode < 200 || $httpcode >= 300) {
            $errormsg = isset($decoded->error->message) ? $decoded->error->message : 'Unknown error';
            $errordetails = isset($decoded->error) ? json_encode($decoded->error) : substr($response, 0, 200);
            debugging("OpenAI API error: HTTP {$httpcode} - {$errordetails}", DEBUG_DEVELOPER);
            throw new \moodle_exception(
                'apierror',
                'local_aiassistant',
                '',
                null,
                "OpenAI API error (HTTP {$httpcode}): {$errormsg}"
            );
        }

        return $decoded;
    }

    /**
     * List all assistants from OpenAI
     *
     * @param string $order Sort order (asc or desc)
     * @param int $limit Maximum number of assistants to return
     * @return object Response containing assistants list
     * @throws \moodle_exception If request fails
     */
    public function list_assistants($order = 'desc', $limit = 100) {
        return $this->get('/assistants', [
            'order' => $order,
            'limit' => $limit,
        ]);
    }

    /**
     * Get assistant info by ID
     *
     * @param string $assistantid Assistant ID
     * @return object Assistant data
     * @throws \moodle_exception If request fails
     */
    public function get_assistant($assistantid) {
        return $this->get('/assistants/' . $assistantid);
    }
}
