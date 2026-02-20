<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'local/reportesgemag:view' => array(
        'captype' => 'view',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => array(
            'manager' => CAP_ALLOW,
            'admin' => CAP_ALLOW,
        ),
    ),
);
