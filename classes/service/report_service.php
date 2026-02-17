<?php

namespace local_reportesgemag\service;

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

            $record = $DB->get_record_sql($sql, [$courseid, $user->id]);

            if ($record && $record->finalgrade !== null) {
                $finalgrade = round($record->finalgrade, 2);
            }

            // Estado simple.
            $status = ($finalgrade !== 'Sin nota') ? 'FINALIZADO' : 'EN PROGRESO';
			
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
