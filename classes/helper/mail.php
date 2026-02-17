<?php

namespace local_reportesgemag\helper;

defined('MOODLE_INTERNAL') || die();

class mail {

    public static function has_mail_been_sent(int $userid, int $courseid, string $mailtype): bool {
        global $DB;

        return $DB->record_exists(
            'local_reportesgemag_mail_log',
            [
                'userid' => $userid,
                'courseid' => $courseid,
                'mailtype' => $mailtype
            ]
        );
    }

    public static function log_mail(int $userid, int $courseid, string $mailtype): void {
        global $DB;

        $record = new \stdClass();
        $record->userid = $userid;
        $record->courseid = $courseid;
        $record->mailtype = $mailtype;
        $record->timecreated = time();

        $DB->insert_record('local_reportesgemag_mail_log', $record);
    }
	
	public static function send_weekly_mail(\stdClass $user, string $coursename): bool {
		$support = \core_user::get_support_user();

		$subject = "Seguimiento del curso: {$coursename}";

		$body = "
		Hola {$user->firstname},

		Este es un mensaje automático de seguimiento del curso:

		{$coursename}

		Puedes acceder aquí:
		" . (new \moodle_url('/course/view.php', ['id' => $user->courseid ?? 0])) . "

		Un saludo.
		";

		return email_to_user($user, $support, $subject, $body);
	}
	
	public static function send_welcome_mail($user, $coursename) {

		global $CFG;

		$subject = "Bienvenido al curso {$coursename}";

		$courseurl = $CFG->wwwroot . "/course/view.php?id=" . $user->courseid;

		$message = "
Hola {$user->firstname},

Te damos la bienvenida al curso:

{$coursename}

Puedes acceder desde aquí:
{$courseurl}

Te recomendamos comenzar lo antes posible.

Un saludo.
";

		email_to_user($user, \core_user::get_support_user(), $subject, $message);

	}
	
}

