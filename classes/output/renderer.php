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

namespace local_aiassistant\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use renderable;
use templatable;
use stdClass;
/**
 * Renderer for AI Assistant
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \plugin_renderer_base {

     /**
     * Renders the AI assistant interface
     * 
     * @return string HTML output
     */
    public function render_ai_assistant() {
        global $CFG;
        
        $data = new stdClass();
        
        // Get icon URL - first try custom icon from settings, then fallback to default
        $iconurl = get_config('local_aiassistant', 'icon');
        if (empty($iconurl)) {
            $data->icon_url = $this->output->image_url('bot-icon', 'local_aiassistant')->out();
        } else {
            $data->icon_url = $iconurl;
        }
        
        // Get current time for message timestamp
        $data->current_time = userdate(time(), get_string('strftimetime', 'core_langconfig'));
        
        // Render the FAB and chat interface
        $fab = $this->render_from_template('local_aiassistant/fab', $data);
        $chat = $this->render_from_template('local_aiassistant/chat', $data);
        
        return $fab . $chat;
    }
}
