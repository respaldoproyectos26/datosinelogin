<?php
ob_start(); // start output buffering
require_once "../../helpers/errores.php";
require_once "../../helpers/auth.php";
require_once "../../helpers/url.php";
require_once "../../helpers/csrf.php";
require_once "../../helpers/helpers_exams.php"; // luego creamos helpers_capacitacion
require_once "../../helpers/flash.php";
require_once "../../helpers/cache_headers.php";
require_login(); csrf_check(); set_cache_headers('html-nocache'); render_flash_swals();
ob_end_flush(); // send all output

if (!can('ver_resumen_asistencia')) { denegar_acceso(); }

$pageTitle = 'Resumen de Asistencia';
$pageHeader = 'Panel General de Capacitación';

$customScripts = [
  '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>',
  '<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>',
  '<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>',
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>'
];
$customStyles = [
  '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">'
];

require_once __DIR__ . '/../../includes/header_base.php';
?>

<div class="container py-4">
  <h3 class="text-center text-white bg-danger p-2 rounded mb-4">
    Panel General de Asistencia a Capacitación
  </h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tablaResumen" class="table table-striped table-bordered align-middle w-100">
          <thead class="table-secondary">
            <tr>
              <th>Usuario</th>
              <th>Tipo</th>
              <th>Fecha</th>
              <th>Inicio</th>
              <th>Fin</th>
              <th>Duración (min)</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  new DataTable('#tablaResumen', {
    ajax: 'api/get_resumen.php',
    serverSide: true,
    processing: true,
    pageLength: 25,
    order: [[2, 'desc']],
    language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
    dom: 'Bfrtip',
    buttons: ['excelHtml5'],
    columns: [
      { data: 'usuario' },
      { data: 'tipo' },
      { data: 'fecha' },
      { data: 'inicio' },
      { data: 'fin' },
      { data: 'duracion_min' }
    ]
  });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer_base.php'; ?>
