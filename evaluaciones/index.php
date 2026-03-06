<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
//require_once "../helpers/helpers_exams.php";
require_login();
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

if (!tiene_permiso('ver_evaluaciones')) {
    denegar_acceso();
}

$examenes = db()->query("SELECT * FROM exams ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Página: Nuevo registro (solo formulario)
$pageTitle  = 'EVALUACIONES';
$pageHeader = 'EVALUACIONES';

$customScripts = [
  '<script src="'.asset('assets/js/sweetalert2-v11.26.18.js').'"></script>',
];

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');
?>

<div class="container my-4">
<?php if (tiene_permiso('crear_evaluaciones')): ?>
    <a href="crear.php" class="btn btn-success mb-3">➕ Crear nueva evaluación</a>
<?php endif; ?>

 <table class="table table-bordered bg-white">
    <thead><tr><th>Título</th><th>Estado</th><th>Ventana</th><th>Acciones</th></tr></thead>
    <tbody>
      <?php foreach ($examenes as $e): ?>
      <tr>
        <td><?= htmlspecialchars($e['title']) ?></td>
        <td><?= $e['status'] ?></td>
        <td>
          <small>
            <?= $e['start_at'] ? "Del {$e['start_at']}" : "—" ?> /
            <?= $e['end_at'] ? "al {$e['end_at']}" : "—" ?>
          </small>
        </td>
        <td class="d-flex gap-2">
          <?php if (tiene_permiso('crear_evaluaciones')): ?>
            <a class="btn btn-sm btn-primary" href="editar.php?id=<?= $e['id'] ?>">Editar</a>
            <a class="btn btn-sm btn-secondary" href="asignar.php?id=<?= $e['id'] ?>">Asignar</a>
          <?php endif; ?>
          <?php if (tiene_permiso('presentar_evaluaciones')): ?>
            <a class="btn btn-sm btn-info" href="presentar.php?id=<?= $e['id'] ?>">Presentar</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<hr>
<?php require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php'); ?>
