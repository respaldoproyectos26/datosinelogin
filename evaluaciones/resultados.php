<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
//require_once "../helpers/helpers_exams.php";
require_login();
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

if (!tiene_permiso('ver_resultados_evaluaciones')) denegar_acceso();

// Cargar lista de exámenes para <select>
$examenes = db()->query("SELECT id, title FROM exams ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$examIdParam = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;

$customStyles = [
  '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">',
];

$customScripts = [
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>',
  '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>',
  '<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>',
];

// 👉 Aquí defines el título y encabezado dinámico
$pageTitle = 'Resultados Evaluaciones';
$pageHeader = 'Resultados Evaluaciones';

require_once "../includes/headerfiltrodatos.php";
?>
<div class="container my-4">
  <div class="d-flex align-items-center gap-2 mb-3">
    <h3 class="mb-0">Resultados de Evaluaciones</h3>
    <span class="text-muted">Modo DataTables + export</span>
  </div>

  <!-- Filtros -->
  <form id="filtros" class="row g-2 mb-3">
    <div class="col-md-4">
      <label class="form-label">Examen</label>
      <select id="f_exam" class="form-select">
        <option value="0">— Todos —</option>
        <?php foreach($examenes as $ex): ?>
          <option value="<?= $ex['id'] ?>" <?= $examIdParam===$ex['id']?'selected':'' ?>>
            <?= htmlspecialchars($ex['title']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" id="f_from" class="form-control">
    </div>
    <div class="col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" id="f_to" class="form-control">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="button" id="btnFiltrar" class="btn btn-primary w-100">Filtrar</button>
    </div>
  </form>

  <!-- KPIs -->
  <div class="row g-3 mb-3" id="kpis" style="display:none;">
    <div class="col-md-3">
      <div class="card p-3">
        <div class="text-muted">Intentos</div>
        <div id="k_total" class="fs-4 fw-bold">0</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <div class="text-muted">Promedio</div>
        <div id="k_prom" class="fs-4 fw-bold">0.0</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <div class="text-muted">Máximo</div>
        <div id="k_max" class="fs-4 fw-bold">0.0</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <div class="text-muted">Mínimo</div>
        <div id="k_min" class="fs-4 fw-bold">0.0</div>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div class="text-muted">Haz clic en “Ver” para abrir la revisión estilo Moodle.</div>
    <div>
      <a id="btnExport" class="btn btn-outline-success btn-sm" href="#">Exportar CSV</a>
    </div>
  </div>

  <table id="tblResultados" class="table table-striped table-bordered bg-white w-100">
    <thead>
      <tr>
        <th>Fecha</th>
        <th>Examen</th>
        <th>Usuario</th>
        <th>Puntaje</th>
        <th>Total</th>
        <th>Duración</th>
        <th>Ver</th>
      </tr>
    </thead>
  </table>
</div>

<!-- DataTables (usa tu CDN habitual si ya lo cargas globalmente) -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">
<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>

<script>
const dt = new DataTable('#tblResultados', {
  processing: true,
  serverSide: true,
  searching: true,
  ajax: (data, callback) => {
    const examId = document.getElementById('f_exam').value || '0';
    const from = document.getElementById('f_from').value || '';
    const to   = document.getElementById('f_to').value || '';
    const params = new URLSearchParams({
      draw: data.draw,
      start: data.start,
      length: data.length,
      search: data.search?.value || '',
      order_col: data.order?.[0]?.column ?? 0,
      order_dir: data.order?.[0]?.dir ?? 'desc',
      exam_id: examId,
      from, to
    });
    fetch('api/exams/resultados_data.php', { method:'POST', body: params })
      .then(r=>r.json())
      .then(j=>{
        // KPIs
        if (j.ok) {
          document.getElementById('k_total').textContent = j.kpis.total ?? 0;
          document.getElementById('k_prom').textContent  = (j.kpis.prom ?? 0).toFixed(2);
          document.getElementById('k_max').textContent   = (j.kpis.max  ?? 0).toFixed(2);
          document.getElementById('k_min').textContent   = (j.kpis.min  ?? 0).toFixed(2);
          document.getElementById('kpis').style.display  = 'flex';
        }
        callback(j);
      });
  },
  columns: [
    { data: 'submitted_at' },
    { data: 'exam_title' },
    { data: 'usuario' },
    { data: 'score',
      render: (v)=> Number(v ?? 0).toFixed(2) },
    { data: 'total_points',
      render: (v)=> Number(v ?? 0).toFixed(2) },
    { data: 'duration_sec',
      render: (v)=> formatDuration(v) },
    { data: 'attempt_id',
      orderable: false,
      render: (id,_,row)=> `<a class="btn btn-sm btn-info" href="ver_intento.php?id=${id}">Ver</a>` }
  ],
  order: [[0,'desc']]
});

document.getElementById('btnFiltrar').addEventListener('click', ()=> dt.ajax.reload());

document.getElementById('btnExport').addEventListener('click', ()=>{
  const examId = document.getElementById('f_exam').value || '0';
  const from = document.getElementById('f_from').value || '';
  const to   = document.getElementById('f_to').value || '';
  const q = new URLSearchParams({ exam_id: examId, from, to }).toString();
  window.location.href = 'api/exams/export_resultados.php?'+q;
});

function formatDuration(s){
  s = Number(s||0);
  const mm = Math.floor(s/60), ss = s%60;
  return mm+':'+String(ss).padStart(2,'0');
}
</script>

<?php require_once "../includes/footerfiltrodatos.php"; ?>
