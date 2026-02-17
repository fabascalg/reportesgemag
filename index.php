<?php

require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/reportesgemag/index.php');
$PAGE->set_context($context);
$PAGE->set_title('Reportes GemaG');
$PAGE->set_heading('Reportes GemaG');

echo $OUTPUT->header();

echo html_writer::tag('h2', 'Dashboard Reportes GemaG');

// Mostrar configuración actual.
$courseid = get_config('local_reportesgemag', 'courseid');
$manageremails = get_config('local_reportesgemag', 'manageremails');

echo html_writer::start_tag('ul');
echo html_writer::tag('li', 'Curso configurado: ' . ($courseid ?: 'No definido'));
echo html_writer::tag('li', 'Emails gestores: ' . ($manageremails ?: 'No definidos'));
echo html_writer::end_tag('ul');

// Placeholder para botones futuros.
echo html_writer::tag('p', 'Aquí añadiremos los botones de ejecución manual.');

echo $OUTPUT->footer();
