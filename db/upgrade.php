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
 * Upgrade script for local_aiassistant
 *
 * @package    local_aiassistant
 * @copyright  2025 Wail Abualela
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function for local_aiassistant
 *
 * @param int $oldversion Old version number
 * @return bool True on success
 */
function xmldb_local_aiassistant_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025101405) {

        // Define table local_aiassistant_threads to be created.
        $table = new xmldb_table('local_aiassistant_threads');

        // Adding fields to table local_aiassistant_threads.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('threadid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_aiassistant_threads.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('user_fk', XMLDB_KEY_FOREIGN, ['userid'], 'user', ['id']);

        // Adding indexes to table local_aiassistant_threads.
        $table->add_index('user_unique', XMLDB_INDEX_UNIQUE, ['userid']);

        // Conditionally launch create table for local_aiassistant_threads.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Aiassistant savepoint reached.
        upgrade_plugin_savepoint(true, 2025101405, 'local', 'aiassistant');
    }

    return true;
}
