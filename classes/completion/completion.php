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
 * Base completion class for AI Assistant
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_aiassistant\completion;

defined('MOODLE_INTERNAL') || die();

/**
 * Base completion class
 */
abstract class completion {

    /** @var string API key */
    protected $apikey;

    /** @var string User message */
    protected $message;

    /** @var array Conversation history */
    protected $history;

    /** @var string AI model */
    protected $model;

    /** @var float Temperature */
    protected $temperature;

    /** @var int Maximum token length */
    protected $maxlength;

    /** @var float Top P */
    protected $topp;

    /** @var float Frequency penalty */
    protected $frequency;

    /** @var float Presence penalty */
    protected $presence;

    /** @var string System prompt */
    protected $prompt;

    /** @var string Assistant name */
    protected $assistantname;

    /** @var string User name */
    protected $username;

    /**
     * Constructor
     *
     * @param string $message User message
     * @param array $history Conversation history
     */
    public function __construct($message, $history) {
        $this->message = $message;
        $this->history = $history;

        // Load settings from plugin configuration.
        $this->apikey = get_config('local_aiassistant', 'apikey');
        $this->model = get_config('local_aiassistant', 'model') ?: 'gpt-3.5-turbo';
        $this->temperature = (float) get_config('local_aiassistant', 'temperature') ?: 0.7;
        $this->maxlength = (int) get_config('local_aiassistant', 'maxlength') ?: 500;
        $this->topp = (float) get_config('local_aiassistant', 'topp') ?: 1.0;
        $this->frequency = (float) get_config('local_aiassistant', 'frequency') ?: 0.0;
        $this->presence = (float) get_config('local_aiassistant', 'presence') ?: 0.0;
        $this->prompt = get_config('local_aiassistant', 'prompt') ?: get_string('defaultprompt', 'local_aiassistant');
        $this->assistantname = get_config('local_aiassistant', 'assistantname') ?: 'AI Assistant';
        $this->username = get_config('local_aiassistant', 'username') ?: 'User';
    }

    /**
     * Create completion - must be implemented by child classes
     *
     * @return array Response data
     */
    abstract public function create_completion();
}
