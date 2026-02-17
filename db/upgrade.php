<?php

defined('MOODLE_INTERNAL') || die();

function xmldb_local_reportesgemag_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026020900) {

        $table = new xmldb_table('local_reportesgemag_mail_log');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('mailtype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_index('user_course_type', XMLDB_INDEX_UNIQUE, ['userid', 'courseid', 'mailtype']);

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026020900, 'local', 'reportesgemag');
    }

    return true;
}
