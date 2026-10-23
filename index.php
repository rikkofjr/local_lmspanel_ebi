<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/plugin_checker.php');

global $DB, $USER, $PAGE, $OUTPUT;

// Proteksi Akses
require_login();
$context = context_system::instance();
if (!is_siteadmin() && !has_capability('moodle/site:viewreports', $context)) {
    throw new moodle_exception('nopermissiontoaccesspage', 'error');
}

// Inisialisasi Halaman
$url = new moodle_url('/local/lmspanel_ebi/index.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('EBI: LMS Control Panel');
$PAGE->set_heading('EBI LMS Control Panel & Health Check');

echo $OUTPUT->header();

$PAGE->navbar->add('EBI LMS Panel');

// Ambil data status plugin
$plugins_data = \local_lmspanel_ebi\plugin_checker::check_status();
$active_count = count(array_filter($plugins_data, function($p) { return $p['is_ok']; }));
$total_plugins = count($plugins_data);
$all_healthy = ($active_count === $total_plugins);
?>

<!-- KOTAK SELAMAT DATANG & TOMBOL MASUK PANEL -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body d-flex justify-content-between align-items-center py-4">
                <div>
                    <h3 class="font-weight-bold text-dark mb-1"><i class="fa fa-shield-alt mr-2 text-primary"></i> EBI LMS Control Panel</h3>
                    <p class="text-muted mb-0">Status kesehatan sistem dan prasyarat modul perusahaan. Pastikan seluruh plugin inti aktif sebelum mengakses panel utama.</p>
                </div>
                <div>
                    <?php if ($all_healthy): ?>
                        <a href="panel.php?tab=map" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fa fa-arrow-right mr-2"></i> Masuk Panel
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled title="Beberapa plugin pendukung belum aktif">
                            <i class="fa fa-exclamation-triangle mr-2"></i> Plugin Belum Lengkap
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL PLUGIN HEALTH CHECK -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><strong><i class="fa fa-heartbeat mr-1 text-danger"></i> Pemeriksaan Status Plugin & Komponen Pendukung</strong></span>
                <span class="badge badge-<?php echo $all_healthy ? 'success' : 'warning'; ?> p-2" style="font-size: 12px;">
                    Status: <?php echo $active_count; ?> / <?php echo $total_plugins; ?> Aktif
                </span>
            </div>
            <div class="card-body">
                <table class="generaltable table table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th style="width: 15%; text-align: center;">Status</th>
                            <th style="width: 25%;">Nama Plugin</th>
                            <th style="width: 25%;">Komponen (Frankenstyle)</th>
                            <th style="width: 15%;">Tipe</th>
                            <th style="width: 20%;">Versi / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($plugins_data as $p): ?>
                        <tr>
                            <td style="text-align: center; vertical-align: middle;">
                                <?php if ($p['is_ok']): ?>
                                    <span class="badge badge-success" style="background-color: #28a745; color: white; padding: 6px 12px;">Aktif</span>
                                <?php else: ?>
                                    <span class="badge badge-danger" style="background-color: #dc3545; color: white; padding: 6px 12px;">Belum Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo s($p['name']); ?></strong><br><small class="text-muted"><?php echo s($p['description']); ?></small></td>
                            <td><code><?php echo s($p['component']); ?></code></td>
                            <td><?php echo s($p['type']); ?></td>
                            <td><?php echo s($p['version']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();