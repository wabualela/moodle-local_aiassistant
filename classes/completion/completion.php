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

    /** @var string User message */
    protected string $message;

    /** @var array Conversation history */
    protected array $history;

    /** @var string System prompt */
    protected string $prompt;

    /** @var string Assistant name */
    protected string $assistantname;

    /** @var string User name */
    protected string $username;

    /** @var int Context ID for the interaction */
    protected int $contextid;

    /** @var int User ID initiating the request */
    protected int $userid;

    /**
     * Constructor
     *
     * @param string $message User message
     * @param array $history Conversation history
     * @param \context $context Moodle context
     * @param int $userid User ID
     * @param string $username Rendered user name
     */
    public function __construct(string $message, array $history, \context $context, int $userid, string $username) {
        $this->message = $message;
        $this->history = $history;
        $this->contextid = $context->id;
        $this->userid = $userid;
        $this->username = $username ?: get_string('user', 'core');

        // Load settings from plugin configuration.
        $this->prompt = (string) (get_config('local_aiassistant', 'prompt')
            ?: get_string('defaultprompt', 'local_aiassistant'));
        $this->assistantname = (string) (get_config('local_aiassistant', 'assistantname')
            ?: get_string('assistant_name', 'local_aiassistant'));
    }

    /**
     * Create completion - must be implemented by child classes
     *
     * @return array Response data
     */
    abstract public function create_completion();

    /**
     * Build a prompt string that captures the system instructions and conversation state.
     *
     * @return string
     */
    protected function build_prompttext(): string {
        $segments = [];

        $systemprompt = trim($this->prompt);
        if ($systemprompt !== '') {
            // Encourage the model to act according to the configured persona.
            $segments[] = $systemprompt;
        }

        $transcript = [];
        foreach ($this->history as $index => $entry) {
            if (!isset($entry['message'])) {
                continue;
            }
            $role = $index % 2 === 0 ? $this->username : $this->assistantname;
            $transcript[] = "{$role}: {$entry['message']}";
        }

        $transcript[] = "{$this->username}: {$this->message}";

        if ($transcript) {
            $segments[] = implode("\n", $transcript);
        }

        // Prompt the model to continue as the assistant.
        $segments[] = "{$this->assistantname}:";

        return implode("\n\n", array_filter($segments));
    }

    /**
     * Expose the context ID for downstream consumers.
     *
     * @return int
     */
    protected function get_contextid(): int {
        return $this->contextid;
    }

    /**
     * Expose the user ID for downstream consumers.
     *
     * @return int
     */
    protected function get_userid(): int {
        return $this->userid;
    }
}
