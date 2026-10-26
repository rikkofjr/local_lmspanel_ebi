<?php
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/local/myidpebi/lib.php');

global $DB, $USER, $PAGE, $OUTPUT;

// 1. Proteksi Akses & Hak Akses
require_login();
$context = context_system::instance();
if (!is_siteadmin() && !has_capability('local/lmspanel_ebi:view', $context)) {
    throw new moodle_exception('nopermissiontoaccesspage', 'error');
}

// 2. Konfigurasi Halaman Moodle
$url = new moodle_url('/local/lmspanel_ebi/panel/idp_dashboard.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('EBI: IDP Management Dashboard');
$PAGE->set_heading('EBI Individual Development Plan (IDP) Analytics');

echo $OUTPUT->header();

// Breadcrumb Navigasi
$PAGE->navbar->add('EBI LMS Panel', new moodle_url('/local/lmspanel_ebi/index.php'));
$PAGE->navbar->add('Control Panel', new moodle_url('/local/lmspanel_ebi/panel.php'));
$PAGE->navbar->add('IDP Dashboard');

// 3. Ambil Tahun dari Parameter (0 = Semua Tahun, atau Tahun Spesifik)
$selected_year = optional_param('year', 0, PARAM_INT);

// 4. Panggil Web Service get_global_analytics
$analytics_data = [
    'year' => $selected_year,
    'total_idp' => 0,
    'completion_rate' => 0.0,
    'total_jp_verified' => 0.0,
    'status_breakdown' => []
];

if (class_exists('\local_myidpebi\external\get_global_analytics')) {
    try {
        $analytics_data = \local_myidpebi\external\get_global_analytics::execute($selected_year);
    } catch (\Exception $e) {
        // Tangani jika terjadi error pemanggilan
    }
}

// 5. Panggil Web Service get_all_user_idps (Default: Semua Status = -1)
$idp_records = [];
if (class_exists('\local_myidpebi\external\get_all_user_idps')) {
    try {
        $idp_records = \local_myidpebi\external\get_all_user_idps::execute(-1);
    } catch (\Exception $e) {
        // Tangani jika terjadi error pemanggilan
    }
}

// Daftar Tahun untuk Dropdown Filter (Dari 2024 s/d Tahun Sekarang)
$start_year = 2024;
$this_year = (int)date('Y');

// Mengambil Definisi Teks & Class Resmi dari lib.php untuk masing-masing status
$status0_info = local_myidpebi_get_status_info(0);
$status1_info = local_myidpebi_get_status_info(1);
$status2_info = local_myidpebi_get_status_info(2);
$status3_info = local_myidpebi_get_status_info(3);
?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-id-card-o mr-2 text-primary"></i> Dashboard Monitoring IDP Karyawan
                </h4>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    Pusat kendali Individual Development Plan (IDP), pelacakan jam pelajaran (JP), dan persetujuan peninjauan.
                </p>
            </div>
            <div class="mt-2 mt-md-0 d-flex align-items-center flex-wrap">
                <!-- DROPDOWN FILTER TAHUN UTAMA (Mempengaruhi KPI Cards & Tabel) -->
                <form method="get" action="" class="form-inline mr-2">
                    <label for="select-year" class="mr-2 font-weight-bold text-secondary" style="font-size: 13px;">Tahun IDP:</label>
                    <select name="year" id="select-year" class="custom-select custom-select-sm" onchange="this.form.submit();">
                        <option value="0" <?php echo ($selected_year == 0) ? 'selected' : ''; ?>>-- Semua Tahun (All-Time) --</option>
                        <?php for ($y = $this_year; $y >= $start_year; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php echo ($selected_year == $y) ? 'selected' : ''; ?>>
                                Tahun <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </form>

                <a href="../panel.php" class="btn btn-outline-secondary btn-sm mr-2">
                    <i class="fa fa-arrow-left mr-1"></i> Kembali ke Panel Hub
                </a>
                <a href="/local/myidpebi/admin_panel.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-external-link mr-1"></i> Panel IDP
                </a>
            </div>
        </div>
    </div>
</div>

<!-- KARTU KPI EKSEKUTIF (Merespons Filter Tahun) -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 bg-primary text-white h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <span class="text-white-50 font-weight-bold" style="font-size: 12px;">
                    TOTAL DOKUMEN IDP (<?php echo ($selected_year == 0) ? 'ALL-TIME' : $selected_year; ?>)
                </span>
                <h2 class="font-weight-bold mb-0 text-white"><?php echo $analytics_data['total_idp']; ?></h2>
                <small class="text-white-50">Diajukan Karyawan</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 bg-success text-white h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <span class="text-white-50 font-weight-bold" style="font-size: 12px;">TINGKAT PENYELESAIAN</span>
                <h2 class="font-weight-bold mb-0 text-white"><?php echo $analytics_data['completion_rate']; ?>%</h2>
                <small class="text-white-50">Verified & Completed</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 bg-info text-white h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <span class="text-white-50 font-weight-bold" style="font-size: 12px;">TOTAL JP TERVERIFIKASI</span>
                <h2 class="font-weight-bold mb-0 text-white"><?php echo number_format($analytics_data['total_jp_verified'], 1, ',', '.'); ?> JP</h2>
                <small class="text-white-50">Akumulasi Jam Pelajaran</small>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card shadow-sm border-0 bg-dark text-white h-100">
            <div class="card-body p-3 d-flex flex-column justify-content-between">
                <span class="text-white-50 font-weight-bold" style="font-size: 12px;">BREAKDOWN STATUS</span>
                <div class="d-flex justify-content-between text-white mt-1" style="font-size: 11px;">
                    <?php foreach ($analytics_data['status_breakdown'] as $sb): ?>
                        <div class="text-center px-1">
                            <span class="d-block font-weight-bold" style="font-size: 14px;"><?php echo $sb['count']; ?></span>
                            <span class="text-white-50"><?php echo htmlspecialchars($sb['status_text']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL UTAMA MONITORING IDP -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="font-weight-bold text-dark mb-0">
                    <i class="fa fa-list mr-2 text-primary"></i> Daftar Dokumen IDP Karyawan
                </h6>
                <div class="form-inline mt-2 mt-md-0">
                    <select id="filter-status-table" class="custom-select custom-select-sm mr-2">
                        <option value="-1">-- Semua Status IDP --</option>
                        <option value="0"><?php echo htmlspecialchars($status0_info->text); ?></option>
                        <option value="1"><?php echo htmlspecialchars($status1_info->text); ?></option>
                        <option value="2"><?php echo htmlspecialchars($status2_info->text); ?></option>
                        <option value="3"><?php echo htmlspecialchars($status3_info->text); ?></option>
                    </select>
                    <div class="input-group input-group-sm">
                        <input type="text" id="search-idp" class="form-control" placeholder="Cari nama karyawan...">
                        <div class="input-group-append">
                            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-idp">
                        <thead class="bg-light text-muted" style="font-size: 12px; text-transform: uppercase;">
                            <tr>
                                <th class="pl-4 py-3" style="width: 50px;">#</th>
                                <th class="py-3">Nama Program IDP & Karyawan</th>
                                <th class="py-3">Pembimbing / Atasan</th>
                                <th class="py-3 text-center">Periode Tanggal</th>
                                <th class="py-3 text-center">JP Verifikasi</th>
                                <th class="py-3 text-center" style="width: 140px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-idp">
                            <?php if (empty($idp_records)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fa fa-info-circle fa-2x mb-2 d-block text-secondary"></i>
                                        Tidak ada data dokumen IDP yang ditemukan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; foreach ($idp_records as $rec): ?>
                                    <?php
                                        // Ambil tahun dari tanggal mulai/dibuat
                                        $idp_year = !empty($rec['mulai_date']) ? (int)date('Y', $rec['mulai_date']) : 0;

                                        // Ambil info status resmi
                                        $st_info = local_myidpebi_get_status_info($rec['status_code']);
                                        $badge_class = !empty($st_info->class) ? $st_info->class : 'badge-secondary';

                                        $mulai = !empty($rec['mulai_date']) ? date('d/m/Y', $rec['mulai_date']) : '-';
                                        $akhir = !empty($rec['akhir_date']) ? date('d/m/Y', $rec['akhir_date']) : '-';
                                    ?>
                                    <tr data-status="<?php echo $rec['status_code']; ?>" data-year="<?php echo $idp_year; ?>">
                                        <td class="pl-4 font-weight-bold text-muted"><?php echo $no++; ?></td>
                                        <td>
                                            <div class="font-weight-bold text-dark">
                                                <a href="/local/myidpebi/view_details.php?id=<?php echo htmlspecialchars($rec['idp_id']);?>">
                                                    <?php echo htmlspecialchars($rec['nama_idp']); ?>
                                                </a>
                                            </div>
                                            <small class="text-muted">
                                                <i class="fa fa-user mr-1"></i> <?php echo htmlspecialchars($rec['user']['firstname'] . ' ' . $rec['user']['lastname']); ?> 
                                                (<code class="text-secondary"><?php echo htmlspecialchars($rec['user']['username']); ?></code>)
                                            </small>
                                        </td>
                                        <td>
                                            <div class="text-dark" style="font-size: 13px;">
                                                <?php echo htmlspecialchars($rec['pembimbing']['firstname'] !== '-' ? $rec['pembimbing']['firstname'] . ' ' . $rec['pembimbing']['lastname'] . ' (' . $rec['pembimbing']['username'] . ')' : '-'); ?>
                                            </div>
                                        </td>
                                        <td class="text-center" style="font-size: 13px;">
                                            <?php echo $mulai; ?> s.d. <?php echo $akhir; ?>
                                        </td>
                                        <td class="text-center font-weight-bold text-primary">
                                            <?php echo number_format($rec['total_jp_verified'], 1, ',', '.'); ?> JP
                                        </td>
                                        <td class="text-center">
                                            <span class="badge <?php echo $badge_class; ?> p-2" style="font-size: 11px;">
                                                <?php echo htmlspecialchars($rec['status_text']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT FILTER CLIENT-SIDE (STATUS, TAHUN & SEARCH) -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var filterStatus = document.getElementById('filter-status-table');
    var searchInput = document.getElementById('search-idp');
    var tbody = document.getElementById('tbody-idp');
    var rows = tbody.getElementsByTagName('tr');

    var selectedYearFromPhp = <?php echo (int)$selected_year; ?>;

    function applyFilter() {
        var statusVal = filterStatus.value;
        var query = searchInput.value.toLowerCase().trim();

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            if (row.cells.length < 2) continue;

            var rowStatus = row.getAttribute('data-status');
            var rowYear = parseInt(row.getAttribute('data-year') || 0);
            var rowText = row.innerText.toLowerCase();

            var matchYear = (selectedYearFromPhp === 0 || rowYear === selectedYearFromPhp);
            var matchStatus = (statusVal === '-1' || rowStatus === statusVal);
            var matchSearch = (query === '' || rowText.includes(query));

            if (matchYear && matchStatus && matchSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }
    }

    filterStatus.addEventListener('change', applyFilter);
    searchInput.addEventListener('input', applyFilter);

    // Jalankan filter saat halaman selesai dimuat
    applyFilter();
});
</script>

<?php
echo $OUTPUT->footer();