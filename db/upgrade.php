<?php
defined('MOODLE_INTERNAL') || die();

// Sertakan file install.php agar fungsi helper import dapat digunakan bersama
require_once(__DIR__ . '/install.php');

function xmldb_local_lmspanel_ebi_upgrade($oldversion) {
    global $CFG;

    // Ganti dengan versi target upgrade Anda (contoh: 2026102301)
    if ($oldversion < 2026102601) {

        // Panggil fungsi import tour dari file JSON
        $tourfile = $CFG->dirroot . '/local/lmspanel_ebi/db/tours/index_tour.json';
        local_lmspanel_ebi_import_tour($tourfile);

        // Simpan titik upgrade savepoint
        upgrade_plugin_savepoint(true, 2026102601, 'local', 'lmspanel_ebi');
    }

    return true;
}