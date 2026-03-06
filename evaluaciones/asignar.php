<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
//require_once "../helpers/helpers_exams.php";
require_login();
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

if (!tiene_permiso('asignar_evaluaciones')) denegar_acceso();

$exam_id = (int)($_GET['id'] ?? 0);
if ($exam_id<=0) { echo "ID inválido"; exit; }

$e = db()->prepare("SELECT * FROM exams WHERE id=?");
$e->execute([$exam_id]);
$exam = $e->fetch(PDO::FETCH_ASSOC);
if (!$exam) { echo "Examen no encontrado"; exit; }

$roles = db()->query("SELECT id,label FROM roles ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$users = db()->query("SELECT id,usuario FROM ine_usuarios ORDER BY usuario")->fetchAll(PDO::FETCH_ASSOC);

$asig = db()->prepare("SELECT user_id, role_id FROM exam_assignments WHERE exam_id=?");
$asig->execute([$exam_id]);
$hasRole = $hasUser = [];
foreach ($asig as $row) {
  if ($row['role_id']) $hasRole[(int)$row['role_id']] = true;
  if ($row['user_id']) $hasUser[(int)$row['user_id']] = true;
}

require_once "../includes/headerfiltrodatos.php";
?>
<div class="container my-4">
  <h3>Asignar examen: <?= htmlspecialchars($exam['title']) ?></h3>

  <form action="api/exams/asignar.php" method="post" class="row g-4">
    <input type="hidden" name="exam_id" value="<?= $exam_id ?>">

    <div class="col-md-6">
      <div class="card p-3">
        <h5>Asignar por roles</h5>
        <?php foreach ($roles as $r): ?>
          <div class="form-check mb-1">
            <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $r['id'] ?>"
              <?= isset($hasRole[$r['id']])?'checked':''; ?>>
            <label class="form-check-label"><?= htmlspecialchars($r['label']) ?></label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card p-3">
        <h5>Asignar por usuarios</h5>
        <div class="border p-2" style="max-height:360px;overflow:auto">
          <?php foreach ($users as $u): ?>
            <div class="form-check mb-1">
              <input class="form-check-input" type="checkbox" name="users[]" value="<?= $u['id'] ?>"
                <?= isset($hasUser[$u['id']])?'checked':''; ?>>
              <label class="form-check-label"><?= htmlspecialchars($u['usuario']) ?></label>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="col-12 d-flex gap-2">
      <button class="btn btn-primary">Guardar asignaciones</button>
      <a href="index.php" class="btn btn-secondary">Volver</a>
    </div>
  </form>
</div>
<?php require_once "../includes/footerfiltrodatos.php"; ?>
