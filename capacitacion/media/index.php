<?php
ob_start(); // start output buffering
require_once "../../helpers/errores.php";
require_once "../../helpers/auth.php";
require_once "../../helpers/url.php";
require_once "../../helpers/csrf.php";
require_once "../../helpers/helpers_media.php"; // luego creamos helpers_capacitacion
require_once "../../helpers/flash.php";
require_once "../../helpers/cache_headers.php";
require_login(); csrf_check(); set_cache_headers('html-nocache'); render_flash_swals();
ob_end_flush(); // send all output

require_login();

if (!auth()->can('ver_biblioteca')) {
    denegar_acceso();
}

$customStyles = [
  '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.1.1/styles/ag-theme-balham.css">',
];

$customScripts = [
  '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>',
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>',
  '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>',
  '<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>',
];

// 👉 Aquí defines el título y encabezado dinámico
$pageTitle = 'Biblioteca de Medios';
$pageHeader = 'Biblioteca de Medios';
?>

<!doctype html>
<html lang="es">
<?php include_once '../../includes/header_base.php'; ?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold"><i class="bi bi-collection-play"></i> Biblioteca de Capacitación</h2>

        <?php if (auth()->can('subir_medios')): ?>
        <a href="subir.php" class="btn btn-success">
            <i class="bi bi-cloud-upload"></i> Subir material
        </a>
        <?php endif; ?>
    </div>

    <div class="row" id="mediaContainer"></div>
    <hr class="my-4">
    <h4 class="mb-3">📂 Administración / Vista tabla</h4>
    <table id="tablaMedia" class="table table-striped table-bordered w-100">
        <thead>
            <tr>
                <th>Título</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

  // Tarjetas
  fetch("api/list.php")
    .then(r=>r.json())
    .then(data=>{
      const container = document.getElementById("mediaContainer");
      container.innerHTML = data.map(item => cardHTML(item)).join("");
    });

  function cardHTML(item){
    return `
    <div class="col-md-4 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="card-title fw-bold">${escapeHtml(item.titulo)}</h5>
          <p class="text-muted small">${escapeHtml(item.categoria || '')}</p>
          <div class="ratio ratio-16x9 mb-2">${renderPreview(item)}</div>
          <a href="ver.php?id=${item.id}" class="btn btn-primary btn-sm">
            <i class="bi bi-eye"></i> Ver
          </a>
          <?php if (auth()->can('editar_material')): ?>
          <a href="editar.php?id=${item.id}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i>
          </a>
          <?php endif; ?>
          <?php if (auth()->can('eliminar_material')): ?>
          <button onclick="eliminar(${item.id})" class="btn btn-danger btn-sm">
            <i class="bi bi-trash"></i>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </div>`;
  }

  function renderPreview(item){
    const base = "../../uploads/media/";
    if (item.tipo === "link") return `<div class="d-flex align-items-center justify-content-center bg-light rounded">
      <i class="bi bi-link-45deg fs-1 text-secondary"></i>
    </div>`;
    if (item.tipo?.startsWith("image")) return `<img src="${base}${encodeURIComponent(item.archivo)}" class="img-fluid rounded">`;
    if (item.tipo === "application/pdf") return `<iframe src="${base}${encodeURIComponent(item.archivo)}#toolbar=0"></iframe>`;
    if (item.tipo?.includes("video")) return `<video controls src="${base}${encodeURIComponent(item.archivo)}"></video>`;
    return `<div class="d-flex align-items-center justify-content-center bg-light rounded">
      <i class="bi bi-file-earmark-text fs-1 text-secondary"></i></div>`;
  }

  function escapeHtml(s){ return (s??'').replace(/[&<>"']/g, m=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[m])); }

  // DataTable Admin
  new DataTable('#tablaMedia', {
    ajax: 'api/list.php?dt=1',
    "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
    },
    columns: [
      { data: 'titulo' },
      { data: 'categoria' },
      { data: 'tipo' },
      { data: 'id',
        orderable:false,
        render: (id,_,row)=> `
          <a class="btn btn-sm btn-primary" href="ver.php?id=${id}">Ver</a>
          <?php if (auth()->can('editar_material')): ?>
          <a class="btn btn-sm btn-warning" href="editar.php?id=${id}">Editar</a>
          <?php endif; ?>
          <?php if (auth()->can('eliminar_material')): ?>
          <button class="btn btn-sm btn-danger" onclick="eliminar(${id})">Eliminar</button>
          <?php endif; ?>
        `
      }
    ],
    order: [[0,'asc']]
  });

});

function eliminar(id){
  if(!confirm("¿Eliminar material?")) return;
  fetch("api/delete.php?id="+id, {method:'POST'})
    .then(r=>r.json())
    .then(j=>{ if(j.ok) location.reload(); else alert(j.error||'Error'); });
}
</script>

<?php require_once "../../includes/footer_base.php"; ?>
