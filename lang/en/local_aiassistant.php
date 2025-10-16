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
 * Strings for component 'local_aiassistant', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package   local_aiassistant
 * @copyright 2025, Wail Abualela wailabualela@alborhan.sa
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'AI Assistant';
$string['open_assistant'] = 'Open AI Assistant';
$string['need_help'] = 'Need help?';
$string['ai_labeltext'] = 'Need help?';
$string['assistant_name'] = 'Moodle AI Support';
$string['options'] = 'Options';
$string['close'] = 'Close';
$string['welcome_message'] = 'Hi! I\'m the Moodle AI Assistant. How can I help you?';
$string['message_placeholder'] = 'Send a message...';
$string['attach_file'] = 'Attach file';
$string['send'] = 'Send message';
$string['powered_by'] = 'Powered by Alborhan';
$string['language_selection'] = 'In what language do you want to continue?';
$string['anything_else'] = 'Is there anything else that you\'d like to know?';
$string['assistantdisabled'] = 'The assistant is currently disabled. Please contact the site administrator.';
$string['aiintegrationerror'] = 'The AI service is unavailable right now. Please try again later or contact the site administrator.';
$string['error_generic'] = 'Sorry, I could not contact the assistant. Please try again later.';
$string['assistantid_required'] = 'Assistant ID is required when using Assistants API mode. Please configure it in the plugin settings.';
$string['assistantid_invalid_format'] = 'Invalid assistant ID format. It should start with "asst_" followed by alphanumeric characters.';
$string['assistantinfo_no_apikey'] = 'OpenAI API key is not configured. Please configure it in Site administration > AI > AI Providers.';
$string['loading'] = 'Loading...';
$string['fetching_assistants'] = 'Fetching assistants from OpenAI...';
// Settings headings.
$string['generalheading'] = 'General Settings';
$string['appearanceheading'] = 'Appearance Settings';
$string['integrationheading'] = 'AI Integration';

// General settings.
$string['enable'] = 'Enable AI Assistant';
$string['enable_desc'] = 'Enable or disable the AI Assistant for all users';

// Appearance settings.
$string['assistantname'] = 'Assistant Name';
$string['assistantname_desc'] = 'The name displayed in the chat header';
$string['fabcolor'] = 'FAB Button Color';
$string['fabcolor_desc'] = 'Color of the floating action button';
$string['fabicon'] = 'FAB Icon';
$string['fabicon_desc'] = 'Upload a custom icon for the FAB button (PNG, JPG, or SVG). Recommended size: 40x40px';
$string['welcomemessage'] = 'Welcome Message';
$string['welcomemessage_desc'] = 'The welcome message displayed to users when they open the chat. You can use HTML formatting.';

// AI integration.
$string['integrationheading_desc'] = 'Credentials, models, and rate limits are now managed via Moodle\'s AI providers. Visit Site administration > AI to configure the OpenAI provider, then enable the actions you want to expose to users.';
$string['apimode'] = 'API Mode';
$string['apimode_desc'] = 'Choose whether to use the standard Completion API or the Assistants API';
$string['apimode_completion'] = 'Completion API (Standard)';
$string['apimode_assistant'] = 'Assistants API (Advanced)';
$string['assistantid'] = 'OpenAI Assistant';
$string['assistantid_desc'] = 'Select your OpenAI Assistant from the dropdown. The list is automatically fetched from your OpenAI account. Create an assistant at platform.openai.com if you haven\'t already.';
$string['prompt'] = 'System Prompt';
$string['prompt_desc'] = 'The system prompt that defines the assistant\'s behavior (used only with Completion API mode)';
$string['defaultprompt'] = 'You are a helpful AI assistant for a Moodle Learning Management System. Provide clear, concise, and accurate assistance to users.';

// API errors.
$string['noproviders'] = 'No AI providers configured';
$string['noapikey'] = 'OpenAI API key not configured';
$string['providererror'] = 'Could not access OpenAI provider';
$string['curlerror'] = 'cURL error occurred';
$string['jsonerror'] = 'Failed to decode JSON response';
$string['apierror'] = 'OpenAI API error';
$string['invalidmethod'] = 'Unsupported HTTP method';
