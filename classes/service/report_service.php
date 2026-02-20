<?php

namespace local_reportesgemag\service;

use completion_info;
use local_reportesgemag\helper\mail as mailhelper;

defined('MOODLE_INTERNAL') || die();

class report_service {

    // =====================================================
    // ORQUESTADOR (si quieres usar cron más adelante)
    // =====================================================
    public static function run_weekly(): array {

        if (!get_config('local_reportesgemag', 'enablecron')) {
           return [];
        }

        $results = [];

        $results = array_merge($results, self::run_students());

        $manager = self::run_manager();
        if (!empty($manager)) {
            $results[] = $manager;
        }

        return $results;
    }

    // =====================================================
    // ALUMNOS
    // =====================================================
    public static function run_students(): array {
        global $DB;

        $results = [];

        $courseid = get_config('local_reportesgemag', 'courseid');
        if (!$courseid) {
            return ['No hay curso configurado'];
        }

        $course = get_course($courseid);
        $completion = new completion_info($course);

        $users = get_enrolled_users(\context_course::instance($courseid));

        foreach ($users as $user) {

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

            $completed = $completion->is_course_complete($user->id);
            
            $passgrade = floatval(get_config('local_reportesgemag', 'passgrade') ?? 7.5);
            $passed = is_numeric($finalgrade) && $finalgrade >= $passgrade; 

            $status = ($completed || $passed) ? 'FINALIZADO' : 'EN PROGRESO';

            // FINISHED
            if ($status === 'FINALIZADO' &&
                !mailhelper::has_mail_been_sent($user->id, $courseid, 'finished')) {

                $user->courseid = $courseid;
                mailhelper::send_finished_mail($user, $course->fullname);
                mailhelper::log_mail($user->id, $courseid, 'finished');
            }

            // WELCOME
            if (!mailhelper::has_mail_been_sent($user->id, $courseid, 'welcome')) {
                $user->courseid = $courseid;
                mailhelper::send_welcome_mail($user, $course->fullname);
                mailhelper::log_mail($user->id, $courseid, 'welcome');
            }

            // WEEKLY
            if (!mailhelper::has_mail_been_sent($user->id, $courseid, 'weekly')) {
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

    // =====================================================
    // GESTORES
    // =====================================================
    public static function run_manager(): string {
        
        if (!get_config('local_reportesgemag', 'enablemanager')) {
            return 'Reporte de gestores desactivado';
            }        
            
        global $DB;

        $courseid = get_config('local_reportesgemag', 'courseid');
        $emails = get_config('local_reportesgemag', 'manageremails');

        if (!$courseid || empty($emails)) {
            return '';
        }

        if (mailhelper::has_mail_been_sent(0, $courseid, 'manager_report')) {
            return 'Reporte de gestores ya enviado';
        }

        $course = get_course($courseid);
        $completion = new completion_info($course);

        $users = get_enrolled_users(\context_course::instance($courseid));

        $managerreport = [];

        foreach ($users as $user) {

            $last = $user->lastaccess ? userdate($user->lastaccess) : 'Nunca accedió';

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

            $completed = $completion->is_course_complete($user->id);
            $passgrade = floatval(get_config('local_reportesgemag', 'passgrade') ?? 7.5);
            $passed = is_numeric($finalgrade) && $finalgrade >= $passgrade;

            $status = ($completed || $passed) ? 'FINALIZADO' : 'EN PROGRESO';

            $managerreport[] =
                "{$user->firstname} {$user->lastname} — {$status} — {$finalgrade} — Último acceso: {$last}";
        }

        mailhelper::send_manager_report(
            $emails,
            $course->fullname,
            implode("\n", $managerreport)
        );

        mailhelper::log_mail(0, $courseid, 'manager_report');

        return 'Reporte enviado a gestores';
    }
}