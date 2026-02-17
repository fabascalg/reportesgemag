<?php

namespace local_reportesgemag\task;

use local_reportesgemag\service\report_service;

defined('MOODLE_INTERNAL') || die();

class weekly_report extends \core\task\scheduled_task {

    public function get_name() {
        return 'Reporte semanal GemaG';
    }

    public function execute() {
        report_service::run_weekly();
    }
}
