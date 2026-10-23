<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig || has_capability('local/lmspanel_ebi:view', context_system::instance())) {
    // Menambahkan menu baru di bawah Site administration > Server atau Courses
    $ADMIN->add('root', new admin_category('local_lmspanel_ebi_category', 'Panel LMS EBI'));

    $temp = new admin_externalpage(
        'local_lmspanel_ebi_dashboard',
        'Executive Control Panel',
        new moodle_url('/local/lmspanel_ebi/index.php'),
        'local/lmspanel_ebi:view'
    );
    
    $ADMIN->add('local_lmspanel_ebi_category', $temp);
}