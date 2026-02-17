<?php

require('../../config.php');

require_login();

use local_reportesgemag\service\report_service;

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

// Curso.
$courselabel = 'No definido';
if ($courseid) {
    if ($course = get_course($courseid)) {
        $courselabel = $course->fullname . " (ID: {$courseid})";
    }
}

// Gestores.
$managerlabel = 'No definidos';
if (!empty($manageremails)) {
    $emails = array_map('trim', explode(',', $manageremails));
    $lines = [];

    foreach ($emails as $email) {
        if ($user = $DB->get_record('user', ['email' => $email])) {
            $lines[] = fullname($user) . " ({$email})";
        } else {
            $lines[] = $email;
        }
    }

    $managerlabel = implode('<br>', $lines);
}

echo html_writer::start_tag('ul');
echo html_writer::tag('li', 'Curso configurado: ' . $courselabel);
echo html_writer::tag('li', 'Gestores:<br>' . $managerlabel);
echo html_writer::end_tag('ul');

$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'runweekly') {

    echo html_writer::tag('h3', 'Resultado ejecución manual');

    $results = report_service::run_weekly();

    if (!empty($results)) {
        echo html_writer::start_tag('ul');
        foreach ($results as $line) {
            echo html_writer::tag('li', $line);
        }
        echo html_writer::end_tag('ul');
    } else {
        echo html_writer::tag('p', 'No hubo resultados.');
    }
}

$url = new moodle_url('/local/reportesgemag/index.php', ['action' => 'runweekly']);

echo html_writer::link($url, 'Ejecutar seguimiento ahora', ['class' => 'btn btn-primary']);



// Placeholder para botones futuros.
echo html_writer::tag('p', 'Aquí añadiremos los botones de ejecución manual.');

echo $OUTPUT->footer();
