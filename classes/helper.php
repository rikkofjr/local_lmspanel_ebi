<?php
namespace local_lmspanel_ebi;

defined('MOODLE_INTERNAL') || die();

class helper {

    public static function get_course_nodes() {
        global $DB;
        
        $sql = "SELECT 
                    c.id AS course_id,
                    c.fullname AS course_name,
                    c.shortname AS course_shortname,
                    GROUP_CONCAT(t.rawname SEPARATOR ', ') AS course_tags
                FROM {course} c
                LEFT JOIN {tag_instance} ti ON ti.itemtype = 'course' AND ti.component = 'core' AND ti.itemid = c.id
                LEFT JOIN {tag} t ON t.id = ti.tagid
                WHERE c.id > 1
                GROUP BY c.id, c.fullname, c.shortname";
                
        return $DB->get_records_sql($sql);
    }

    public static function get_course_edges() {
        global $DB;
        $edges = [];

        // Ambil semua course yang memiliki pengaturan section atau modul
        // Kita bisa memindai tabel course_sections atau course_modules yang menyimpan data availability
        $sections = $DB->get_records_sql("SELECT id, course, availability FROM {course_sections} WHERE availability IS NOT NULL AND availability != ''");

        foreach ($sections as $section) {
            $data = json_decode($section->availability);
            if ($data) {
                self::parse_availability_tree($data, $section->course, $edges);
            }
        }

        // Juga cek di tingkat course_modules jika ada pembatasan di level aktivitas
        $modules = $DB->get_records_sql("SELECT id, course, availability FROM {course_modules} WHERE availability IS NOT NULL AND availability != ''");

        foreach ($modules as $mod) {
            $data = json_decode($mod->availability);
            if ($data) {
                self::parse_availability_tree($data, $mod->course, $edges);
            }
        }

        // Hapus duplikat edges jika ada
        return array_unique($edges, SORT_REGULAR);
    }

    private static function parse_availability_tree($node, $target_course_id, &$edges) {
        // Jika node ini adalah tipe prerequisite plugin availability_prerequisite
        if (isset($node->type) && $node->type === 'prerequisite' && isset($node->course)) {
            $source_course_id = (int)$node->course;
            $edges[] = (object)[
                'source_course_id' => $source_course_id,
                'target_course_id' => (int)$target_course_id
            ];
        }

        // Jika berbentuk tree kondisi gabungan (AND / OR) yang memiliki anak (c)
        if (isset($node->c) && is_array($node->c)) {
            foreach ($node->c as $child) {
                self::parse_availability_tree($child, $target_course_id, $edges);
            }
        }
    }
}