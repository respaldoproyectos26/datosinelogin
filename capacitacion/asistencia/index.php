<?php
require_once __DIR__ . '/../../helpers/bootstrap.php';
require_login(); csrf_check(); set_cache_headers('html-nocache');

if (!can('ver_asistencia_capacitacion')) {
    flash('error','Acceso denegado');
    redirect('../index.php');
}

$pageTitle = 'Asistencia de Capacitación';
$pageHeader = 'Registros de Visualización';

$customScripts = [
  '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>'
];
$customStyles = [
  '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">'
];

require_once __DIR__ . '/../../includes/header_base.php';

$pdo = db();

// Historial de visualizaciones
$rows = $pdo->query("
  SELECT s.id, u.usuario, m.titulo, s.inicio, s.fin, s.duracion_sec
  FROM media_sesiones s
  JOIN ine_usuarios u ON u.id = s.user_id
  JOIN media m ON m.id = s.media_id
  ORDER BY s.inicio DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container py-4">

  <!-- ========================== -->
  <!-- SECCIÓN 1: Asistencia manual -->
  <!-- ========================== -->
  <h3 class="mb-4 text-center text-white bg-danger p-2 rounded">Registro de Asistencia</h3>

  <div class="card shadow-sm mb-4">
    <div class="card-body text-center">
      <button id="btnEntrada" class="btn btn-success btn-lg">Registrar Entrada</button>
      <button id="btnSalida" class="btn btn-danger btn-lg ms-3" disabled>Registrar Salida</button>
      <p class="mt-3" id="mensaje" class="fw-bold text-primary"></p>
    </div>
  </div>

  <div class="card shadow-sm mb-5">
    <div class="card-header bg-secondary text-white">Historial de Asistencias Manuales</div>
    <div class="card-body" id="tablaAsistencia">
      <div class="text-center text-muted">Cargando historial...</div>
    </div>
  </div>


  <!-- ========================== -->
  <!-- SECCIÓN 2: Asistencia automática (Media) -->
  <!-- ========================== -->
  <h3 class="fw-bold mb-4"><i class="bi bi-clock-history"></i> Visualizaciones de Material</h3>

  <div class="card shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table id="asistencia" class="table table-striped table-bordered align-middle">
          <thead class="table-secondary">
            <tr>
              <th>Usuario</th>
              <th>Material</th>
              <th>Inicio</th>
              <th>Fin</th>
              <th>Duración (min)</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($rows as $r): ?>
              <tr>
                <td><?= e($r['usuario']) ?></td>
                <td><?= e($r['titulo']) ?></td>
                <td><?= e($r['inicio']) ?></td>
                <td><?= e($r['fin']) ?></td>
                <td><?= round($r['duracion_sec']/60, 1) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<script src="js/asistencia.js?ver=<?=date('YmdHis')?>"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  new DataTable('#asistencia', {
    order: [[2, 'desc']],
    pageLength: 10,
    language: {
      url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
    }
  });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer_base.php'; ?>