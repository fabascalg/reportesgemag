<?php

namespace local_reportesgemag\service;

use completion_info;
use local_reportesgemag\helper\mail as mailhelper;

defined('MOODLE_INTERNAL') || die();

class report_service {

    public static function run_weekly(): array {
        global $DB;

        $results = [];

        $courseid = get_config('local_reportesgemag', 'courseid');
        if (!$courseid) {
            return ['error' => 'No hay curso configurado'];
        }

        $course = get_course($courseid);
		
		$completion = new completion_info($course);

        $users = get_enrolled_users(\context_course::instance($courseid));

        foreach ($users as $user) {

            // Último acceso.
            $last = $user->lastaccess ? userdate($user->lastaccess) : 'Nunca accedió';

            // Nota final.
            $finalgrade = 'Sin nota';
            $sql = "
                SELECT gg.finalgrade
                FROM {grade_items} gi
                JOIN {grade_grades} gg ON gg.itemid = gi.id
                WHERE gi.courseid = ?
                  AND gi.itemtype = 'course'
                  AND gg.userid = ?
            ";
;

            $record = $DB->get_record_sql($sql, [$courseid, $user->id]);

            if ($record && $record->finalgrade !== null) {
                $finalgrade = round($record->finalgrade, 2);
            }
			
			// AQUÍ va completion + passed + status

			$completed = $completion->is_course_complete($user->id);

			$passed = is_numeric($finalgrade) && $finalgrade >= 7.5;
			
			// --- SIMULACIÓN TEMPORAL ---
			if ($user->email === 'nndoff@gmail.com') {
				$completed = true;
			}			

			$status = ($completed || $passed) ? 'FINALIZADO' : 'EN PROGRESO';	

			// FINISHED (solo una vez).
			if ($status === 'FINALIZADO') {

				if (!mailhelper::has_mail_been_sent($user->id, $courseid, 'finished')) {

					$user->courseid = $courseid;

					mailhelper::send_finished_mail($user, $course->fullname);

					mailhelper::log_mail($user->id, $courseid, 'finished');

					$finishedflag = 'FINISHED ENVIADO';

				} else {
					$finishedflag = 'FINISHED YA REGISTRADO';
				}

			} else {
				$finishedflag = '';
			}			
			
			// WELCOME (solo una vez).
			if (!mailhelper::has_mail_been_sent($user->id, $courseid, 'welcome')) {
				$user->courseid = $courseid; // para URL
				mailhelper::send_welcome_mail($user, $course->fullname);
				mailhelper::log_mail($user->id, $courseid, 'welcome');
			}
			
            // Weekly mail.
            $already = mailhelper::has_mail_been_sent($user->id, $courseid, 'weekly');

            if (!$already) {
                $user->courseid = $courseid;
                mailhelper::send_weekly_mail($user, $course->fullname);
                mailhelper::log_mail($user->id, $courseid, 'weekly');
                $flag = 'EMAIL ENVIADO';
            } else {
                $flag = 'YA REGISTRADO';
            }

            $results[] = "{$user->firstname} {$user->lastname} — {$status} — {$flag}";
        }

        return $results;
    }
}
