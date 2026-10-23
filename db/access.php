<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // Kemampuan dasar untuk melihat dashboard panel EBI
    'local/lmspanel_ebi:view' => [
        'riskbitmask' => RISK_PERSONAL,
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW,
        ]
    ],
    // Kemampuan tingkat lanjut untuk mengubah konfigurasi plugin/plugin checker
    'local/lmspanel_ebi:manage' => [
        'riskbitmask' => RISK_CONFIG,
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'admin' => CAP_ALLOW
        ]
    ]
];