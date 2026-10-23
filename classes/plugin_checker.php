<?php
namespace local_lmspanel_ebi;

defined('MOODLE_INTERNAL') || die();

class plugin_checker {

    public static function get_monitored_plugins() {
        return [
            [
                'name' => 'Core Course Completion',
                'component' => 'core_completion',
                'type' => 'core',
                'description' => 'Prasyarat dasar kelulusan course.'
            ],
            [
                'name' => 'Program',
                'component' => 'tool_muprog',
                'type' => 'tool',
                'description' => 'Pengelolaan Learning Program. https://docs.mutms.org/muprog/installation/#installing-from-zip-packages'
            ],
            [
                'name' => 'Restriction by Course (Prerequisite)',
                'component' => 'availability_prerequisite',
                'type' => 'availability', // 
                'description' => 'Plugin utama pengatur alur prasyarat antar course. https://marketplace.moodle.com/plugins/3818'
            ],
            [
                'name' => 'Block Daily Practice',
                'component' => 'block_daily_practice',
                'type' => 'block', // 
                'description' => 'Plugin mapping daily practice karyawan. https://github.com/rikkofjr/block_daily_practice'
            ],
            [
                'name' => 'Local Atasan Monitoring',
                'component' => 'local_atasan_monitoring',
                'type' => 'local', // 
                'description' => 'Plugin check course yang sudah terenrol oleh bawahan. https://github.com/rikkofjr/local_atasan_monitoring'
            ],
            [
                'name' => 'Dashboard EBI',
                'component' => 'local_dashboard_ebi',
                'type' => 'local', // 
                'description' => 'Plugin visualisasi penyelesaian course dan IDP milik karyawan pribadi dan teamnya. https://github.com/rikkofjr/dashboard_ebi'
            ],
            [
                'name' => 'Booking Room DC',
                'component' => 'local_bookingroomdc',
                'type' => 'local', // 
                'description' => 'Plugin untuk booking ruangan & absensi kehadiran. https://github.com/rikkofjr/local_bookingroomdc'
            ],
            [
                'name' => 'My IDP EBI',
                'component' => 'local_myidpebi',
                'type' => 'local',
                'description' => 'Modul manajemen Individual Development Plan perusahaan. https://github.com/rikkofjr/local_myidpebi'
            ]
        ];
    }

    public static function check_status() {
        $plugins = self::get_monitored_plugins();
        $results = [];

        foreach ($plugins as $p) {
            $status = 'missing';
            $version = '-';

            if ($p['type'] === 'core') {
                $status = 'ok';
                $version = 'Built-in Core';
            } else {
                // Di dalam method check_status() pada kelas plugin_checker.php
                $pluginman = \core_plugin_manager::instance();
                $plugininfo = $pluginman->get_plugin_info($p['component']);

                if ($plugininfo) {
                    // Jika tipe plugin adalah local dan foldernya ada serta versi terdeteksi, 
                    // kita anggap OK (karena local plugin umumnya otomatis aktif jika terpasang)
                    if ($p['type'] === 'local') {
                        if (!empty($plugininfo->versiondb)) {
                            $status = 'ok';
                            $version = $plugininfo->versiondb;
                        } else {
                            $status = 'disabled';
                            $version = 'Needs Upgrade/Install';
                        }
                    } else {
                        // Untuk plugin jenis lain (enrol, availability, dll) ikuti fungsi bawaan
                        if ($plugininfo->is_enabled()) {
                            $status = 'ok';
                            $version = $plugininfo->versiondb ?? 'Installed';
                        } else {
                            $status = 'disabled';
                            $version = 'Disabled';
                        }
                    }
                }
            }

            $results[] = [
                'name' => $p['name'],
                'component' => $p['component'],
                'type' => $p['type'],
                'description' => $p['description'],
                'status' => $status,
                'version' => $version,
                'is_ok' => ($status === 'ok')
            ];
        }

        return $results;
    }
}