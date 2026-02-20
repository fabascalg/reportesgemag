<?php

require_once(__DIR__ . '/../../config.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

use local_reportesgemag\service\report_service;

$PAGE->set_url('/local/reportesgemag/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Reportes GemaG');
$PAGE->set_heading('Dashboard Reportes GemaG');

echo $OUTPUT->header();

echo "<h2>Dashboard Reportes GemaG</h2>";

$courseid = get_config('local_reportesgemag', 'courseid');
$emails = get_config('local_reportesgemag', 'manageremails');

if ($courseid) {
    $course = get_course($courseid);
    echo "<p><strong>Curso configurado:</strong> {$course->fullname} (ID: {$courseid})</p>";
}

if ($emails) {
    echo "<p><strong>Gestores:</strong> {$emails}</p>";
}

// ===============================
// Procesar acciones
// ===============================
$results = [];

if (optional_param('run_students', false, PARAM_BOOL)) {
    $results = report_service::run_students();
}

if (optional_param('run_manager', false, PARAM_BOOL)) {
    $msg = report_service::run_manager();
    if ($msg) {
        $results[] = $msg;
    }
}

if (optional_param('clear_logs', false, PARAM_BOOL)) {
    global $DB;
    $DB->execute("TRUNCATE {local_reportesgemag_mail_log}");
    $results[] = "Registros limpiados";
}

// ===============================
// Mostrar resultados
// ===============================
if (!empty($results)) {
    echo "<h3>Resultado</h3><ul>";
    foreach ($results as $r) {
        echo "<li>{$r}</li>";
    }
    echo "</ul>";
}

// ===============================
// Botones
// ===============================
echo '
<form method="post">
    <button type="submit" name="run_students" value="1">Ejecutar seguimiento alumnos</button>
    <button type="submit" name="run_manager" value="1">Enviar reporte gestores</button>
    <button type="submit" name="clear_logs" value="1"
        onclick="return confirm(\'¿Seguro que quieres limpiar los registros?\')">
        Limpiar registros
    </button>
</form>
';

echo $OUTPUT->footer();