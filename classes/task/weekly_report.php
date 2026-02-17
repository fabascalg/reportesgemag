<?php

namespace local_reportesgemag\task;

// plus 2602081206
use local_reportesgemag\helper\mail as mailhelper;

defined('MOODLE_INTERNAL') || die();

class weekly_report extends \core\task\scheduled_task {

    public function get_name() {
        return 'Reporte semanal GemaG';
    }

    public function execute() {
        global $DB, $CFG;

        // Necesario para notas.
		require_once($CFG->libdir . '/completionlib.php');

        debugging('=== Reportes GemaG ejecutándose ===', DEBUG_DEVELOPER);

        // Curso configurado en settings.
        $courseid = get_config('local_reportesgemag', 'courseid');

        if (empty($courseid)) {
            debugging('No hay curso configurado.', DEBUG_DEVELOPER);
            return;
        }

        // Obtener curso.
        $course = get_course($courseid);
        debugging('Curso: ' . $course->fullname, DEBUG_DEVELOPER);

        // Contexto del curso.
        $context = \context_course::instance($courseid);

        // Obtener usuarios matriculados.
        $users = get_enrolled_users($context);

        if (empty($users)) {
            debugging('No hay usuarios matriculados.', DEBUG_DEVELOPER);
            return;
        }

        // API de finalización.
        $completion = new \completion_info($course);

        foreach ($users as $user) {

            // Último acceso al curso.
            $lastaccess = $DB->get_field(
                'user_lastaccess',
                'timeaccess',
                [
                    'userid' => $user->id,
                    'courseid' => $courseid
                ]
            );

            $last = $lastaccess ? userdate($lastaccess) : 'Nunca accedió';

            // ¿Curso completado?
            $iscomplete = $completion->is_course_complete($user->id);
            $status = $iscomplete ? 'FINALIZADO' : 'EN PROGRESO';

            // Nota final del curso.
            // Nota final del curso (método compatible).
			$finalgrade = 'Sin nota';

			$sql = "
				SELECT gg.finalgrade
				FROM {grade_items} gi
				JOIN {grade_grades} gg ON gg.itemid = gi.id
				WHERE gi.courseid = ?
				  AND gi.itemtype = 'course'
				  AND gg.userid = ?
			";

			$record = $DB->get_record_sql($sql, [$courseid, $user->id]);

			if ($record && $record->finalgrade !== null) {
				$finalgrade = round($record->finalgrade, 2);
			}
			
			$already = mailhelper::has_mail_been_sent($user->id, $courseid, 'weekly');

			if (!$already) {

				// Enviar correo semanal.
				$user->courseid = $courseid; // pequeño truco para la URL
				mailhelper::send_weekly_mail($user, $course->fullname);

				// Registrar envío.
				mailhelper::log_mail($user->id, $courseid, 'weekly');

				$flag = 'EMAIL ENVIADO';

			} else {
				$flag = 'YA REGISTRADO';
			}




            // Log del alumno.
			debugging(
				"Alumno: {$user->firstname} {$user->lastname} - {$user->email} - Último acceso: {$last} - Nota: {$finalgrade} - Estado: {$status} - {$flag}",
				DEBUG_DEVELOPER
			);

        }

        debugging('=== Fin Reportes GemaG ===', DEBUG_DEVELOPER);
    }
}
