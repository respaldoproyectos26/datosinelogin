<?php
require_once 'helpers/errores.php';
require_once 'helpers/cache_headers.php';
require_once 'helpers/db.php';
require_once 'helpers/prefix.php';
require_once 'helpers/csrf.php';
require_once 'helpers/auth.php';
require_once 'helpers/url.php';
require_once 'helpers/flash.php';

require_login(); // esto valida correctamente que esté logueado
set_cache_headers('api');

require_permission('ver_panel_roles');

$user_id = (int)($_GET['user_id'] ?? 0);

$roles = db()->query("SELECT * FROM roles ORDER BY label")->fetchAll(PDO::FETCH_ASSOC);
$stmt = db()->prepare("SELECT role_id FROM role_user WHERE user_id = ?");
$stmt->execute([$user_id]);
$roles_usuario = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<form class="ajax-form mx-auto" action="guardar_roles.php" method="post">
  <?php csrf_input(); ?>
  <input type="hidden" name="user_id" value="<?= $user_id ?>">
  <?php foreach ($roles as $r): ?>
    <div class="form-check">
      <input type="checkbox" class="form-check-input" name="roles[]" value="<?= $r['id'] ?>"
        <?= in_array($r['id'], $roles_usuario) ? 'checked' : '' ?>>
      <label class="form-check-label"><?= htmlspecialchars($r['label']) ?></label>
    </div>
  <?php endforeach; ?>
  <div class="d-flex justify-content-center mt-3">
    <button class="btn btn-primary btn-sm">Guardar cambios</button>
  </div>
</form>
