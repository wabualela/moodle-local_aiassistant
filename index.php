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
 * TODO describe file index
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela <wailabualela@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$url = new moodle_url('/local/aiassistant/index.php', []);
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$PAGE->set_heading($SITE->fullname);
echo $OUTPUT->header();


$manager       = \core\di::get(\core_ai\manager::class);
$providers     = $manager->get_provider_instances();
$firstProvider = reset($providers);
$firstProvider = current($providers);
$apikey        = $firstProvider->config['apikey'];

$curl = new curl();
$curl->setopt(array(
    'CURLOPT_HTTPHEADER' => array(
        'Authorization: Bearer ' . $apikey,
        'Content-Type: application/json',
        'OpenAI-Beta: assistants=v2',
    ),
));

$response        = $curl->get("https://api.openai.com/v1/assistants?order=desc");
$response        = json_decode($response);
$assistant_array = [];
if (property_exists($response, 'data')) {
    foreach ($response->data as $assistant) {
        $assistant_array[ $assistant->id ] = $assistant->name;
    }
}

die(var_dump(
    $assistant_array
));

echo $OUTPUT->footer();
