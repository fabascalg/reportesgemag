<?php

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/adminlib.php');

if ($hassiteconfig) {

    // Página principal de configuración.
    $settings = new admin_settingpage(
        'local_reportesgemag',
        'Reportes GemaG'
    );

    /*
     * 1. Activar / Desactivar plugin
     */
    $settings->add(new admin_setting_configcheckbox(
        'local_reportesgemag/enabled',
        'Activar Reportes GemaG',
        'Si está desactivado no se enviarán correos ni se ejecutará seguimiento.',
        1
    ));

    /*
     * 2. Obtener lista de cursos
     */
    $courses = get_courses();
    $courselist = [];

    foreach ($courses as $course) {
        if ($course->id != SITEID) {
            $courselist[$course->id] = $course->fullname . ' (ID: ' . $course->id . ')';
        }
    }

    /*
     * 3. Selector de curso
     */
    $settings->add(new admin_setting_configselect(
        'local_reportesgemag/courseid',
        'Curso a monitorizar',
        'Selecciona el curso sobre el que se realizará el seguimiento.',
        '',
        $courselist
    ));

    /*
     * 4. Emails gestores
     */
    $settings->add(new admin_setting_configtext(
        'local_reportesgemag/manageremails',
        'Emails gestores',
        'Separados por coma.',
        '',
        PARAM_TEXT
    ));

    // Añadir página de configuración al árbol.
    $ADMIN->add('localplugins', $settings);

    // Añadir enlace al Dashboard.
    $ADMIN->add(
        'localplugins',
        new admin_externalpage(
            'local_reportesgemag_dashboard',
            'Dashboard Reportes GemaG',
            new moodle_url('/local/reportesgemag/index.php')
        )
    );
}
