<?php
ob_start(); // start output buffering

require_once __DIR__ . '/../helpers/bootstrap.php';

require_login(); 
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

ob_end_flush(); // send all output

if (!can('ver_capacitacion')) { denegar_acceso(); }

$pageTitle = 'Capacitación';
$pageHeader = 'Panel de Capacitación';
?>

<?php include_once '../includes/header.php'; ?>

<div class="container py-4">
  <div class="row text-center mb-4">
    <h2 class="fw-bold text-info"><i class="bi bi-mortarboard"></i> Capacitación</h2>
    <p class="text-muted">Selecciona una opción para comenzar</p>
  </div>

  <div class="row g-4">
    <?php if (can('ver_biblioteca')): ?>
    <div class="col-md-4">
      <a href="biblioteca/" class="text-decoration-none">
        <div class="card h-100 shadow-sm hover-shadow text-center p-4">
          <i class="bi bi-collection-play display-4 text-primary"></i>
          <h5 class="fw-bold mt-3">Biblioteca de Medios</h5>
          <p class="text-muted small">Material audiovisual y documentos de capacitación</p>
        </div>
      </a>
    </div>
    <?php endif; ?>

    <div class="col-md-4">
      <a href="media/" class="text-decoration-none">
        <div class="card h-100 shadow-sm hover-shadow text-center p-4">
          <i class="bi bi-camera-video display-4 text-info"></i>
          <h5 class="fw-bold mt-3">Medios</h5>
          <p class="text-muted small">Videos y recursos de capacitación</p>
        </div>
      </a>
    </div>

    <div class="col-md-4">
      <a href="../evaluaciones/" class="text-decoration-none">
        <div class="card h-100 shadow-sm hover-shadow text-center p-4">
          <i class="bi bi-ui-checks-grid display-4 text-success"></i>
          <h5 class="fw-bold mt-3">Evaluaciones</h5>
          <p class="text-muted small">Evalúa tus conocimientos</p>
        </div>
      </a>
    </div>

    <?php if (can('ver_asistencia_capacitacion')): ?>
    <div class="col-md-4">
      <a href="asistencia/" class="text-decoration-none">
        <div class="card h-100 shadow-sm hover-shadow text-center p-4">
          <i class="bi bi-clock-history display-4 text-warning"></i>
          <h5 class="fw-bold mt-3">Asistencia</h5>
          <p class="text-muted small">Control de accesos y tiempos vistos</p>
        </div>
      </a>
    </div>
    <?php endif; ?>

    <?php if (can('ver_reportes')): ?>
    <div class="col-md-4">
      <a href="reportes/" class="text-decoration-none">
        <div class="card h-100 shadow-sm hover-shadow text-center p-4">
          <i class="bi bi-file-earmark-text display-4 text-primary"></i>
          <h5 class="fw-bold mt-3">Reportes</h5>
          <p class="text-muted small">Accesos a información y estadísticas de Asistencia</p>
        </div>
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>
<style>.hover-shadow:hover{transform:scale(1.02);transition:.2s;box-shadow:0 0 15px rgba(0,0,0,.2)}</style>
<?php require_once __DIR__ . '/../includes/footerfiltrodatos.php'; ?>
