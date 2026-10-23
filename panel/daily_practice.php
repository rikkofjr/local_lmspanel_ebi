<?php
require_once(__DIR__ . '/../../../config.php');

global $DB, $USER, $PAGE, $OUTPUT;

// 1. Proteksi Akses & Hak Akses
require_login();
$context = context_system::instance();
if (!is_siteadmin() && !has_capability('moodle/site:viewreports', $context)) {
    throw new moodle_exception('nopermissiontoaccesspage', 'error');
}

// 2. Konfigurasi Halaman Moodle
$url = new moodle_url('/local/lmspanel_ebi/panel/daily_practice.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('EBI: Daily Practice Dashboard');
$PAGE->set_heading('EBI Daily Practice Analytics');

echo $OUTPUT->header();

// Breadcrumb Navigasi
$PAGE->navbar->add('EBI LMS Panel', new moodle_url('/local/lmspanel_ebi/index.php'));
$PAGE->navbar->add('Control Panel', new moodle_url('/local/lmspanel_ebi/panel.php'));
$PAGE->navbar->add('Daily Practice Dashboard');

// 3. Ambil Data Mapping Jabatan dari API External
$job_mappings = [];
if (class_exists('\block_daily_practice\external\get_job_mappings')) {
    $response = \block_daily_practice\external\get_job_mappings::execute();
    if (!empty($response['status']) && !empty($response['data'])) {
        $job_mappings = $response['data'];
    }
}
?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-tasks mr-2 text-primary"></i> Dashboard Daily Practice Karyawan
                </h4>
                <p class="text-muted mb-0" style="font-size: 13px;">
                    Pemantauan keaktifan, pengerjaan kuis harian, dan kepatuhan latihan berbasis level jabatan.
                </p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="../panel.php" class="btn btn-outline-secondary btn-sm mr-2">
                    <i class="fa fa-arrow-left mr-1"></i> Kembali ke Panel Hub
                </a>
                <button class="btn btn-sm btn-outline-primary" onclick="location.reload();">
                    <i class="fa fa-refresh mr-1"></i> Refresh Data
                </button>
                <a href="/blocks/daily_practice/admin_manage.php" class="btn btn-outline-secondary btn-sm ml-2">
                    <i class="fa fa-book mr-1"></i> Manage
                </a>
            </div>
        </div>
    </div>
</div>

<!-- FILTER PANEL & KARTU KPI RINGKASAN -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <label for="select-jabatan" class="font-weight-bold text-dark mb-2">
                    <i class="fa fa-filter mr-1 text-primary"></i> Pilih Level Jabatan:
                </label>
                <select id="select-jabatan" class="custom-select custom-select-lg">
                    <option value="">-- Pilih Level Jabatan --</option>
                    <?php foreach ($job_mappings as $map): ?>
                        <option value="<?php echo htmlspecialchars($map['jabatan_value']); ?>" 
                                data-course="<?php echo htmlspecialchars($map['course_fullname']); ?>">
                            <?php echo strtoupper(htmlspecialchars($map['jabatan_value'])); ?> 
                            (Course ID: <?php echo $map['course_id']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted mt-2">
                    Pilih jabatan untuk memuat rekapitulasi progress pengerjaan kuis harian.
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="row h-100">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="card shadow-sm border-0 bg-primary text-white h-100">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <span class="text-white-50 font-weight-bold" style="font-size: 12px;">TOTAL PARTISIPAN</span>
                        <h2 class="font-weight-bold mb-0 text-white" id="stat-total-user">0</h2>
                        <small class="text-white-50">Karyawan Terdaftar</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="card shadow-sm border-0 bg-success text-white h-100">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <span class="text-white-50 font-weight-bold" style="font-size: 12px;">RATA-RATA PENCAPAIAN</span>
                        <h2 class="font-weight-bold mb-0 text-white" id="stat-avg-completion">0%</h2>
                        <small class="text-white-50">Penyelesaian Kuis</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-info text-white h-100">
                    <div class="card-body p-3 d-flex flex-column justify-content-between">
                        <span class="text-white-50 font-weight-bold" style="font-size: 12px;">COURSE ACUAN</span>
                        <h6 class="font-weight-bold mb-0 text-white text-truncate" id="stat-course-name">-</h6>
                        <small class="text-white-50">Alih fungsi course menjadi daily practice</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TABEL REKAPITULASI PARTISIPAN -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap">
                <h6 class="font-weight-bold text-dark mb-0">
                    <i class="fa fa-users mr-2 text-primary"></i> Rekapitulasi Progress Partisipan Daily Practice
                </h6>
                <div class="form-inline mt-2 mt-md-0">
                    <div class="input-group input-group-sm">
                        <input type="text" id="search-participant" class="form-control" placeholder="Cari nama karyawan..." disabled>
                        <div class="input-group-append">
                            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="table-participants">
                        <thead class="bg-light text-muted" style="font-size: 12px; text-transform: uppercase;">
                            <tr>
                                <th class="pl-4 py-3" style="width: 50px;">#</th>
                                <th class="py-3">Karyawan</th>
                                <th class="py-3">Username</th>
                                <th class="py-3 text-center">Kuis Diselesaikan</th>
                                <th class="py-3" style="width: 220px;">Progress Penyelesaian</th>
                                <th class="py-3 text-center" style="width: 120px;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-participants">
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa fa-info-circle fa-2x mb-2 d-block text-secondary"></i>
                                    Silakan pilih <b>Level Jabatan</b> di atas untuk menampilkan data partisipan.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT FETCH DATA AJAX VIA WEBSERVICE API -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var selectJabatan = document.getElementById('select-jabatan');
    var tbody = document.getElementById('tbody-participants');
    var searchInput = document.getElementById('search-participant');

    var statTotalUser = document.getElementById('stat-total-user');
    var statAvgCompletion = document.getElementById('stat-avg-completion');
    var statCourseName = document.getElementById('stat-course-name');

    var currentParticipantsData = [];

    // Event listener saat Jabatan dipilih
    selectJabatan.addEventListener('change', function() {
        var jabatanValue = this.value;
        var selectedOption = this.options[this.selectedIndex];
        var courseName = selectedOption.getAttribute('data-course') || '-';

        if (!jabatanValue) {
            resetDashboard();
            return;
        }

        // Tampilkan Indikator Loading
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa fa-spinner fa-spin fa-2x mb-2 d-block text-primary"></i>Memuat data partisipan...</td></tr>';
        statCourseName.innerText = courseName;

        // Panggil Web Service Moodle via AJAX Internal
        var wsUrl = M.cfg.wwwroot + '/webservice/rest/server.php';
        
        // Kita menggunakan endpoint internal AJAX Moodle
        fetch(M.cfg.wwwroot + '/lib/ajax/service.php?sesskey=' + M.cfg.sesskey, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify([{
                index: 0,
                methodname: 'block_daily_practice_get_participants_summary',
                args: { jabatan_value: jabatanValue }
            }])
        })
        .then(function(res) { return res.json(); })
        .then(function(response) {
            if (response[0] && !response[0].error && response[0].data && response[0].data.status) {
                currentParticipantsData = response[0].data.data || [];
                renderTable(currentParticipantsData);
                searchInput.disabled = false;
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger"><i class="fa fa-exclamation-triangle mr-1"></i> Gagal mengambil data partisipan dari server.</td></tr>';
            }
        })
        .catch(function(err) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger"><i class="fa fa-exclamation-triangle mr-1"></i> Terjadi kesalahan koneksi.</td></tr>';
        });
    });

    // Fungsi Render Tabel Partisipan & KPI
    function renderTable(data) {
        if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada karyawan yang terdaftar untuk jabatan ini.</td></tr>';
            statTotalUser.innerText = '0';
            statAvgCompletion.innerText = '0%';
            return;
        }

        var rowsHtml = '';
        var totalPersentase = 0;

        data.forEach(function(p, index) {
            totalPersentase += p.persentase;

            // Penentuan Warna Progress Bar & Badge
            var barColor = 'bg-danger';
            var badgeClass = 'badge-danger';
            var badgeText = 'Rendah';

            if (p.persentase >= 80) {
                barColor = 'bg-success';
                badgeClass = 'badge-success';
                badgeText = 'Tinggi';
            } else if (p.persentase >= 50) {
                barColor = 'bg-warning';
                badgeClass = 'badge-warning';
                badgeText = 'Sedang';
            }

            rowsHtml += '<tr>' +
                '<td class="pl-4 font-weight-bold text-muted">' + (index + 1) + '</td>' +
                '<td>' +
                    '<div class="font-weight-bold text-dark">' + escapeHtml(p.firstname + ' ' + p.lastname) + '</div>' +
                '</td>' +
                '<td><code class="text-secondary">' + escapeHtml(p.username) + '</code></td>' +
                '<td class="text-center font-weight-bold">' + p.total_benar + ' / ' + p.jumlah_soal + '</td>' +
                '<td>' +
                    '<div class="d-flex align-items-center">' +
                        '<div class="progress flex-grow-1 mr-2" style="height: 8px; border-radius: 4px;">' +
                            '<div class="progress-bar ' + barColor + '" role="progressbar" style="width: ' + p.persentase + '%;"></div>' +
                        '</div>' +
                        '<span class="font-weight-bold" style="font-size: 12px; width: 45px;">' + p.persentase + '%</span>' +
                    '</div>' +
                '</td>' +
                '<td class="text-center"><span class="badge ' + badgeClass + ' p-2" style="font-size: 11px;">' + badgeText + '</span></td>' +
            '</tr>';
        });

        tbody.innerHTML = rowsHtml;

        // Update Kartu KPI
        statTotalUser.innerText = data.length;
        var avg = (totalPersentase / data.length).toFixed(1);
        statAvgCompletion.innerText = avg + '%';
    }

    // Live Search Filter di Tabel Partisipan
    searchInput.addEventListener('input', function(e) {
        var q = e.target.value.toLowerCase().trim();
        var filtered = currentParticipantsData.filter(function(p) {
            var fullName = (p.firstname + ' ' + p.lastname).toLowerCase();
            return fullName.includes(q) || p.username.toLowerCase().includes(q);
        });
        renderTable(filtered);
    });

    function resetDashboard() {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted"><i class="fa fa-info-circle fa-2x mb-2 d-block text-secondary"></i>Silakan pilih <b>Level Jabatan</b> di atas untuk menampilkan data partisipan.</td></tr>';
        statTotalUser.innerText = '0';
        statAvgCompletion.innerText = '0%';
        statCourseName.innerText = '-';
        searchInput.disabled = true;
        searchInput.value = '';
    }

    function escapeHtml(text) {
        return text.replace(/[&<>"']/g, function(m) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
        });
    }
});
</script>

<?php
echo $OUTPUT->footer();