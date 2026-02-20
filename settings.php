<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    // Página de settings.
    $settings = new admin_settingpage(
        'local_reportesgemag',
        'Reportes GemaG'
    );

    // Añadir settings al bloque plugins locales.
    $ADMIN->add('localplugins', $settings);

    // === DASHBOARD LINK ===
    $ADMIN->add(
        'localplugins',
        new admin_externalpage(
            'local_reportesgemag_dashboard',
            'Dashboard Reportes GemaG',
            new moodle_url('/local/reportesgemag/index.php')
        )
    );

    // Activar / desactivar plugin.
    $settings->add(new admin_setting_configcheckbox(
        'local_reportesgemag/enabled',
        'Activar Reportes GemaG',
        'Si está desactivado no se enviarán correos ni se ejecutará seguimiento.',
        1
    ));

    // Lista de cursos.
    $courses = get_courses();
    $courselist = [];

    foreach ($courses as $course) {
        if ($course->id != SITEID) {
            $courselist[$course->id] = $course->fullname;
        }
    }

    // Curso a monitorizar.
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

    // Activar manager report.
    $settings->add(new admin_setting_configcheckbox(
        'local_reportesgemag/enablemanager',
        'Activar reporte a gestores',
        'Enviar reporte agregado a gestores.',
        1
    ));

    // Nota mínima.
    $settings->add(new admin_setting_configtext(
        'local_reportesgemag/passgrade',
        'Nota mínima de aprobación',
        'Por defecto 7.5',
        '7.5',
        PARAM_FLOAT
    ));

    // Activar cron.
    $settings->add(new admin_setting_configcheckbox(
        'local_reportesgemag/enablecron',
        'Activar cron automático',
        'Permite que el seguimiento se ejecute por cron.',
        0
    ));    
    
}