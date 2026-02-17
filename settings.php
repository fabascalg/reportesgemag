<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings = new admin_settingpage(
        'local_reportesgemag',
        'Reportes GemaG'
    );

    $ADMIN->add('localplugins', $settings);

    // Obtener lista de cursos.
    $courses = get_courses();
    $courselist = [];

    foreach ($courses as $course) {
        if ($course->id != SITEID) {
            $courselist[$course->id] = $course->fullname;
        }
    }

    // Selector de curso REAL.
    $settings->add(new admin_setting_configselect(
        'local_reportesgemag/courseid',
        'Curso a monitorizar',
        '',
        '',
        $courselist
    ));

    // Emails gestores.
    $settings->add(new admin_setting_configtext(
        'local_reportesgemag/manageremails',
        'Emails gestores',
        'Separados por coma',
        '',
        PARAM_TEXT
    ));
}
