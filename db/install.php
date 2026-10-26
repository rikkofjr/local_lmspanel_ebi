<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_lmspanel_ebi_install() {
    global $CFG;
    
    // Path menuju file JSON tur
    $tourfile = $CFG->dirroot . '/local/lmspanel_ebi/db/tours/index_tour.json';
    local_lmspanel_ebi_import_tour($tourfile);

    return true;
}

/**
 * Fungsi helper untuk membaca dan memasukkan User Tour dari file JSON ke database.
 */
function local_lmspanel_ebi_import_tour($tourfile) {
    global $DB;

    if (!file_exists($tourfile)) {
        return;
    }

    $rawdata = file_get_contents($tourfile);
    $tourdata = json_decode($rawdata, true);

    if (!$tourdata || empty($tourdata['name'])) {
        return;
    }

    // Cek apakah tour dengan nama yang sama sudah ada di database
    $existing = $DB->get_record('tool_usertours_tours', ['name' => $tourdata['name']]);
    if ($existing) {
        return; // Jangan duplikasi jika sudah ada
    }

    // 1. Simpan data utama Tour
    $tour = new \stdClass();
    $tour->name = $tourdata['name'];
    $tour->description = $tourdata['description'] ?? '';
    $tour->pathmatch = $tourdata['pathmatch'] ?? '';
    $tour->enabled = $tourdata['enabled'] ?? 1;
    $tour->sortorder = $tourdata['sortorder'] ?? 1;
    $tour->configdata = is_array($tourdata['configdata']) ? json_encode($tourdata['configdata']) : $tourdata['configdata'];

    $tourid = $DB->insert_record('tool_usertours_tours', $tour);

    if ($tourid && !empty($tourdata['steps'])) {
        // 2. Simpan setiap langkah (steps) tur
        foreach ($tourdata['steps'] as $stepdata) {
            $step = new \stdClass();
            $step->tourid = $tourid;
            $step->title = $stepdata['title'] ?? '';
            $step->content = $stepdata['content'] ?? '';
            $step->targettype = $stepdata['targettype'] ?? 0;
            $step->targetvalue = $stepdata['targetvalue'] ?? '';
            $step->sortorder = $stepdata['sortorder'] ?? 0;
            $step->configdata = is_array($stepdata['configdata']) ? json_encode($stepdata['configdata']) : $stepdata['configdata'];

            $DB->insert_record('tool_usertours_steps', $step);
        }
    }
}