<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
//require_once "../helpers/helpers_exams.php";
require_login();
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

if (!tiene_permiso('crear_evaluaciones')) denegar_acceso();

$exam_id = (int)($_GET['id'] ?? 0);
if ($exam_id<=0) { echo "ID inválido"; exit; }

$st = db()->prepare("SELECT * FROM exams WHERE id=?");
$st->execute([$exam_id]);
$exam = $st->fetch(PDO::FETCH_ASSOC);
if (!$exam) { echo "Examen no encontrado"; exit; }

$qs = db()->prepare("SELECT * FROM questions WHERE exam_id=? ORDER BY id ASC");
$qs->execute([$exam_id]);
$preguntas = $qs->fetchAll(PDO::FETCH_ASSOC);

require_once "../includes/headerfiltrodatos.php";
?>
<div class="container my-4">
  <h3>Editar examen: <?= htmlspecialchars($exam['title']) ?></h3>

  <form action="api/exams/update_examen.php" method="post" class="card p-3 mb-4">
    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label">Mostrar puntaje</label><br>
        <input type="checkbox" name="show_score" <?= $exam['show_score']?'checked':'' ?>>
      </div>
      <div class="col-md-3"><label class="form-label">Mostrar correctas</label><br>
        <input type="checkbox" name="show_correct_answers" <?= $exam['show_correct_answers']?'checked':'' ?>>
      </div>
      <div class="col-md-3"><label class="form-label">Permitir revisión</label><br>
        <input type="checkbox" name="show_review" <?= $exam['show_review']?'checked':'' ?>>
      </div>
      <div class="col-md-3"><label class="form-label">Retro al final</label><br>
        <input type="checkbox" name="show_detailed_feedback" <?= $exam['show_detailed_feedback']?'checked':'' ?>>
      </div>
    </div>
    <div class="row g-3 mt-2">
      <div class="col-md-3"><label>Estado</label>
        <select name="status" class="form-select">
          <option value="draft"     <?= $exam['status']=='draft'?'selected':'' ?>>Borrador</option>
          <option value="published" <?= $exam['status']=='published'?'selected':'' ?>>Publicado</option>
          <option value="closed"    <?= $exam['status']=='closed'?'selected':'' ?>>Cerrado</option>
        </select>
      </div>
      <div class="col-md-3"><label>Inicio</label>
        <input type="datetime-local" name="start_at" class="form-control"
               value="<?= $exam['start_at'] ? date('Y-m-d\TH:i', strtotime($exam['start_at'])):'' ?>">
      </div>
      <div class="col-md-3"><label>Fin</label>
        <input type="datetime-local" name="end_at" class="form-control"
               value="<?= $exam['end_at'] ? date('Y-m-d\TH:i', strtotime($exam['end_at'])):'' ?>">
      </div>
      <div class="col-md-3"><label>Aleatorizar</label><br>
        <input type="checkbox" name="shuffle_questions" <?= $exam['shuffle_questions']?'checked':'' ?>>
      </div>
    </div>
    <button class="btn btn-primary mt-3">Guardar configuración</button>
  </form>

  <form action="api/exams/agregar_pregunta.php" method="post" enctype="multipart/form-data" class="card p-3 mb-4">
    <h5>Agregar Pregunta</h5>
    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">
    <div class="mb-2">
      <label>Tipo</label>
      <select name="type" class="form-select" required>
        <option value="mcq">Opción múltiple</option>
        <option value="truefalse">Verdadero/Falso</option>
        <option value="text">Respuesta abierta</option>
      </select>
    </div>
    <div class="mb-2">
      <label>Enunciado</label>
      <textarea name="stem" class="form-control" required></textarea>
    </div>
    <div class="mb-2">
      <label>Multimedia (opcional)</label>
      <input type="file" name="media" class="form-control" accept="image/*,audio/*">
    </div>
    <div class="mb-2">
      <label>Opciones (solo MCQ)</label>
      <input type="text" name="options[]" class="form-control mb-1" placeholder="Opción A">
      <input type="text" name="options[]" class="form-control mb-1" placeholder="Opción B">
      <input type="text" name="options[]" class="form-control mb-1" placeholder="Opción C">
      <input type="text" name="options[]" class="form-control mb-1" placeholder="Opción D">
    </div>
    <div class="mb-2">
      <label>Respuesta correcta (MCQ: texto exacto / T-F: V o F)</label>
      <input type="text" name="correct" class="form-control" placeholder="Ej: Opción A  |  V  |  F">
    </div>
    <button class="btn btn-success">Agregar pregunta</button>
  </form>

  <h4>Preguntas</h4>
  <table class="table table-bordered bg-white">
    <thead><tr><th>ID</th><th>Tipo</th><th>Puntos</th><th>Enunciado</th></tr></thead>
    <tbody>
      <?php foreach ($preguntas as $p): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><?= $p['type'] ?></td>
          <td><?= $p['points'] ?></td>
          <td><?= htmlspecialchars($p['stem']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <a href="index.php" class="btn btn-secondary mt-3">Volver</a>
</div>
<?php require_once "../includes/footerfiltrodatos.php"; ?>
