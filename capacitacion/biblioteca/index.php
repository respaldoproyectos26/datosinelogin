<?php
require_once __DIR__ . '/../../helpers/bootstrap.php';
require_once __DIR__ . '/../../helpers/helpers_media.php';
require_login(); csrf_check(); set_cache_headers('html-nocache'); render_flash_swals();

if (!can('ver_biblioteca')) { flash('error','Acceso denegado'); redirect('../bienvenida.php'); }

$pageTitle = 'Biblioteca';
$pageHeader = 'Capacitación - Biblioteca';

$customStyles = [
  '<link rel="stylesheet" href="https://cdn.datatables.net/2.1.7/css/dataTables.dataTables.min.css">'
];
$customScripts = [
  '<script src="https://cdn.datatables.net/2.1.7/js/dataTables.min.js"></script>',
  '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>',
];
require_once __DIR__ . '/../../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="fw-bold"><i class="bi bi-collection-play"></i> Biblioteca</h2>
    <?php if (can('subir_medios')): ?>
      <a href="subir.php" class="btn btn-success"><i class="bi bi-cloud-upload"></i> Subir material</a>
    <?php endif; ?>
  </div>

  <div class="row" id="mediaContainer"></div>

  <hr class="my-4">
  <h4 class="mb-3">Administración</h4>
  <table id="tablaMedia" class="table table-striped table-bordered w-100">
    <thead><tr><th>Título</th><th>Categoría</th><th>Tipo</th><th>Acciones</th></tr></thead>
  </table>
</div>

<script>
function escapeHtml(s){return (s??'').replace(/[&<>"']/g,m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));}
function renderPreview(item){
  const base = "uploads/";
  if (item.tipo === "link") return `<div class="d-flex align-items-center justify-content-center bg-light rounded"><i class="bi bi-link-45deg fs-1 text-secondary"></i></div>`;
  if (item.tipo?.startsWith("image")) return `<img src="${base}${encodeURIComponent(item.archivo)}" class="img-fluid rounded">`;
  if (item.tipo === "application/pdf") return `<iframe src="${base}${encodeURIComponent(item.archivo)}#toolbar=0"></iframe>`;
  if (item.tipo?.includes("video")) return `<video controls src="${base}${encodeURIComponent(item.archivo)}"></video>`;
  return `<div class="d-flex align-items-center justify-content-center bg-light rounded"><i class="bi bi-file-earmark-text fs-1 text-secondary"></i></div>`;
}

document.addEventListener("DOMContentLoaded", ()=>{
  fetch("api/list.php")
    .then(r=>r.json())
    .then(data=>{
      document.getElementById("mediaContainer").innerHTML =
        data.map(item => `
          <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5 class="card-title fw-bold">${escapeHtml(item.titulo)}</h5>
                <p class="text-muted small">${escapeHtml(item.categoria||'')}</p>
                <div class="ratio ratio-16x9 mb-2">${renderPreview(item)}</div>
                <a href="ver.php?id=${item.id}" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Ver</a>
                <?php if (can('editar_material')): ?>
                <a href="editar.php?id=${item.id}" class="btn btn-warning btn-sm"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
                <?php if (can('eliminar_material')): ?>
                <button class="btn btn-danger btn-sm" onclick="eliminar(${item.id})"><i class="bi bi-trash"></i></button>
                <?php endif; ?>
              </div>
            </div>
          </div>
        `).join('');
    });

  new DataTable('#tablaMedia', {
    ajax: 'api/list.php?dt=1',
    columns: [
      { data:'titulo' }, { data:'categoria' }, { data:'tipo' },
      { data:'id', orderable:false,
        render:(id)=> `
          <a class="btn btn-sm btn-primary" href="ver.php?id=${id}">Ver</a>
          <?php if (can('editar_material')): ?><a class="btn btn-sm btn-warning" href="editar.php?id=${id}">Editar</a><?php endif; ?>
          <?php if (can('eliminar_material')): ?><button class="btn btn-sm btn-danger" onclick="eliminar(${id})">Eliminar</button><?php endif; ?>
        `
      }
    ],
    order: [[0,'asc']],
    "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
  });
});

function eliminar(id){
  if(!confirm("¿Eliminar material?")) return;
  fetch("api/delete.php", {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF':'<?= csrf_token() ?>'},
    body:new URLSearchParams({id})
  })
  .then(r=>r.json())
  .then(j=>{ if(j.ok) location.reload(); else alert(j.error||'Error');});
}
</script>
<?php require_once __DIR__ . '/../../includes/footer_base.php'; ?>
