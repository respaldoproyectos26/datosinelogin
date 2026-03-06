<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
//require_once "../helpers/helpers_exams.php";
require_login();
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

if (!tiene_permiso('crear_evaluaciones')) {
    denegar_acceso();
}

// Página: Nuevo registro (solo formulario)
$pageTitle  = 'CREAR EVALUACION';
$pageHeader = 'CREAR EVALUACION';

$customScripts = [
  '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>',
];

?>
<?php require_once "../includes/headerfiltrodatos.php"; ?>

<div class="container my-4">
  <h3>Crear Evaluación</h3>
  <form action="api/exams/crear_examen.php" method="post" class="card p-3 bg-white">
    <div class="mb-3">
      <label class="form-label">Título</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descripción</label>
      <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="row g-3">
      <div class="col-md-3">
        <label class="form-label">Límite (min)</label>
        <input type="number" name="time_limit_min" class="form-control" value="0">
      </div>
      <div class="col-md-3">
        <label class="form-label">Intentos</label>
        <input type="number" name="attempts_allowed" class="form-control" value="1" min="1">
      </div>
      <div class="col-md-3">
        <label class="form-label">Inicio</label>
        <input type="datetime-local" name="start_at" class="form-control">
      </div>
      <div class="col-md-3">
        <label class="form-label">Fin</label>
        <input type="datetime-local" name="end_at" class="form-control">
      </div>
    </div>
    <div class="form-check mt-3">
      <input class="form-check-input" type="checkbox" name="shuffle_questions" checked>
      <label class="form-check-label">Aleatorizar preguntas</label>
    </div>
    <button class="btn btn-success mt-3">Guardar</button>
    <a href="index.php" class="btn btn-secondary mt-3">Cancelar</a>
  </form>
</div>

<?php require_once "../includes/footerfiltrodatos.php"; ?>
