<?php
require_once(__DIR__ . '/../../config.php');

global $DB, $USER, $PAGE, $OUTPUT;

// Proteksi Akses
require_login();
$context = context_system::instance();
if (!is_siteadmin() && !has_capability('moodle/site:viewreports', $context)) {
    throw new moodle_exception('nopermissiontoaccesspage', 'error');
}

$url = new moodle_url('/local/lmspanel_ebi/panel.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('EBI: Executive Control Panel Hub');
$PAGE->set_heading('EBI Executive Control Center');

echo $OUTPUT->header();

$PAGE->navbar->add('EBI LMS Panel', new moodle_url('/local/lmspanel_ebi/index.php'));
$PAGE->navbar->add('Control Panel Hub');
?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div>
                    <h3 class="font-weight-bold text-dark mb-1"><i class="fa fa-th-large mr-2 text-primary"></i> EBI Control Panel Hub</h3>
                    <p class="text-muted mb-0">Pilih modul analitik atau pemetaan kurikulum perusahaan di bawah ini.</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fa fa-heartbeat mr-1"></i> Cek Kesehatan Plugin
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DAFTAR KARTU MENU PANEL (Akan bertambah seiring waktu) -->
<div class="row">
    <!-- Menu 1: Course & Prerequisite Mapping -->
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fa fa-sitemap fa-3x text-primary"></i>
                </div>
                <h5 class="card-title font-weight-bold">Course Mapping</h5>
                <p class="card-text text-muted" style="font-size: 13px;">Memetakan struktur kurikulum, prasyarat antar course, ID, dan tags dalam diagram interaktif.</p>
                <a href="panel/mapping_course.php" class="btn btn-primary btn-block mt-3">
                    <i class="fa fa-arrow-right mr-1"></i> Buka Mapping Course
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fa fa-book fa-3x text-primary"></i>
                </div>
                <h5 class="card-title font-weight-bold">Daily Practice</h5>
                <p class="card-text text-muted" style="font-size: 13px;">Laporan Terikini Pelaksanaan Daily Practice.<br/><br/></p>
                <a href="panel/daily_practice.php" class="btn btn-primary btn-block mt-3">
                    <i class="fa fa-arrow-right mr-1"></i> Buka
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="mb-3">
                    <i class="fa fa-book fa-3x text-primary"></i>
                </div>
                <h5 class="card-title font-weight-bold">MYIDP EBI</h5>
                <p class="card-text text-muted" style="font-size: 13px;">Monitoring pelaksanaan IDP yang telah dilakukan karyawan.<br/></p>
                <a href="panel/idp_dashboard.php" class="btn btn-primary btn-block mt-3">
                    <i class="fa fa-arrow-right mr-1"></i> Buka
                </a>
            </div>
        </div>
    </div>

    <!-- TEMPAT MENAMBAH KARTU MENU BARU DI MASA DEPAN:
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body text-center p-4">
                <div class="mb-3"><i class="fa fa-chart-line fa-3x text-success"></i></div>
                <h5 class="card-title font-weight-bold">Menu Baru</h5>
                <p class="card-text text-muted" style="font-size: 13px;">Deskripsi modul baru...</p>
                <a href="panel/menu_baru.php" class="btn btn-success btn-block mt-3">Buka Modul</a>
            </div>
        </div>
    </div>
    -->
</div>

<?php
echo $OUTPUT->footer();