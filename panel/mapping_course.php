<?php
require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../classes/helper.php');

global $DB, $USER, $PAGE, $OUTPUT;

// Proteksi Akses
require_login();
$context = context_system::instance();
if (!is_siteadmin() && !has_capability('local/lmspanel_ebi:view', $context)) {
    throw new moodle_exception('nopermissiontoaccesspage', 'error');
}

$url = new moodle_url('/local/lmspanel_ebi/panel/mapping_course.php');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title('EBI: Course & Prerequisite Mapping');
$PAGE->set_heading('EBI Course Mapping');

echo $OUTPUT->header();

$PAGE->navbar->add('EBI LMS Panel', new moodle_url('/local/lmspanel_ebi/index.php'));
$PAGE->navbar->add('Control Panel', new moodle_url('/local/lmspanel_ebi/panel.php'));
$PAGE->navbar->add('Course Mapping');

$nodes = \local_lmspanel_ebi\helper::get_course_nodes();
$edges = \local_lmspanel_ebi\helper::get_course_edges();
?>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h4 class="font-weight-bold text-dark mb-1"><i class="fa fa-sitemap mr-2 text-primary"></i> Peta Prasyarat Course</h4>
                <p class="text-muted mb-0" style="font-size: 13px;">Diagram hirarki interaktif dengan fitur pencarian dan mode layar penuh.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="../panel.php" class="btn btn-outline-secondary btn-sm mr-2">
                    <i class="fa fa-arrow-left mr-1"></i> Kembali ke Panel Hub
                </a>
                <button class="btn btn-sm btn-outline-primary mr-2" onclick="location.reload();">
                    <i class="fa fa-refresh mr-1"></i> Reset Tampilan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- KONTROL BAR: PENCARIAN & FULLSCREEN -->
<div class="row mb-3">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 bg-light">
            <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap">
                <div class="form-inline my-1" style="width: 100%; max-width: 400px;">
                    <div class="input-group w-100">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="search-course" class="form-control" placeholder="Cari nama atau ID course...">
                    </div>
                </div>
                <div class="my-1">
                    <button id="btn-fullscreen" class="btn btn-sm btn-dark">
                        <i class="fa fa-expand mr-1"></i> Layar Penuh (Fullscreen)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KOTAK DIAGRAM -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0" id="map-card-container">
            <div class="card-body p-0">
                <!-- Canvas Cytoscape -->
                <div id="cy-course-map" style="width: 100%; height: 700px; background-color: #f8f9fa;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Load Cytoscape.js & Dagre Layout via CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.28.1/cytoscape.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dagre/0.8.5/dagre.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cytoscape-dagre@2.5.0/cytoscape-dagre.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var rawNodes = <?php echo json_encode(array_values($nodes)); ?>;
    var rawEdges = <?php echo json_encode(array_values($edges)); ?>;

    var cyElements = [];

    // Masukkan Nodes dengan ukuran dinamis berbasis label
    rawNodes.forEach(function(c) {
        var labelText = c.course_name + " \n[ID: " + c.course_id + "]";
        if (c.course_tags) {
            labelText += "\nTags: " + c.course_tags;
        }
        cyElements.push({
            data: { 
                id: 'c_' + c.course_id, 
                label: labelText,
                name: c.course_name.toLowerCase(),
                cid: String(c.course_id)
            }
        });
    });

    // Masukkan Edges (Garis Prasyarat)
    rawEdges.forEach(function(e) {
        if(e.source_course_id && e.target_course_id) {
            cyElements.push({
                data: { source: 'c_' + e.source_course_id, target: 'c_' + e.target_course_id }
            });
        }
    });

    // Inisialisasi Cytoscape dengan Dagre Layout
    var cy = cytoscape({
        container: document.getElementById('cy-course-map'),
        elements: cyElements,
        style: [
            {
                selector: 'node',
                style: {
                    'shape': 'round-rectangle',
                    'background-color': '#ffffff',
                    'border-color': '#0f6cbf',
                    'border-width': 2,
                    'label': 'data(label)',
                    'color': '#1e293b',
                    'text-wrap': 'wrap',
                    'text-valign': 'center',
                    'text-halign': 'center',
                    'font-size': '12px',
                    'font-family': 'sans-serif',
                    'padding': '16px',
                    'width': 'label',
                    'height': 'label',
                    'min-width': '160px',
                    'min-height': '50px'
                }
            },
            {
                selector: 'edge',
                style: {
                    'width': 2,
                    'line-color': '#94a3b8',
                    'target-arrow-color': '#94a3b8',
                    'target-arrow-shape': 'triangle',
                    'curve-style': 'bezier'
                }
            },
            {
                selector: ':selected',
                style: {
                    'background-color': '#e3f2fd',
                    'border-color': '#0d47a1',
                    'border-width': 3
                }
            },
            // Style saat node disorot pencarian
            {
                selector: '.search-match',
                style: {
                    'background-color': '#d4edda',
                    'border-color': '#28a745',
                    'border-width': 4
                }
            },
            {
                selector: '.search-dimmed',
                style: {
                    'opacity': 0.25
                }
            }
        ],
        layout: {
            name: 'dagre',
            rankDir: 'LR',
            nodeSep: 50,
            rankSep: 100,
            padding: 30
        }
    });

    // FITUR PENCARIAN REAL-TIME
    var searchInput = document.getElementById('search-course');
    searchInput.addEventListener('input', function(e) {
        var query = e.target.value.toLowerCase().trim();

        if (query === '') {
            cy.elements().removeClass('search-match search-dimmed');
            return;
        }

        cy.batch(function() {
            cy.elements().addClass('search-dimmed').removeClass('search-match');
            
            var matchedNodes = cy.nodes().filter(function(node) {
                var name = node.data('name');
                var cid = node.data('cid');
                return name.includes(query) || cid.includes(query);
            });

            if (matchedNodes.length > 0) {
                matchedNodes.removeClass('search-dimmed').addClass('search-match');
                // Fokus otomatis ke node pertama yang cocok
                cy.animate({
                    center: { cole: matchedNodes.first() },
                    zoom: 1.2
                }, { duration: 500 });
            }
        });
    });

    // FITUR FULLSCREEN
    var btnFullscreen = document.getElementById('btn-fullscreen');
    var cardContainer = document.getElementById('map-card-container');
    var cyContainer = document.getElementById('cy-course-map');

    btnFullscreen.addEventListener('click', function() {
        if (!document.fullscreenElement) {
            if (cardContainer.requestFullscreen) {
                cardContainer.requestFullscreen();
            } else if (cardContainer.webkitRequestFullscreen) {
                cardContainer.webkitRequestFullscreen();
            }
            cyContainer.style.height = '85vh';
            btnFullscreen.innerHTML = '<i class="fa fa-compress mr-1"></i> Keluar Fullscreen';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            }
            cyContainer.style.height = '700px';
            btnFullscreen.innerHTML = '<i class="fa fa-expand mr-1"></i> Layar Penuh (Fullscreen)';
        }
        // Resize ulang ukuran kanvas grafik setelah masuk/keluar fullscreen
        setTimeout(function() { cy.resize(); }, 300);
    });
});
</script>

<?php
echo $OUTPUT->footer();