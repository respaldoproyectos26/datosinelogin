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

$role_id = (int)($_GET['role_id'] ?? 0);

$permisos = db()->query("SELECT * FROM permissions ORDER BY label")->fetchAll(PDO::FETCH_ASSOC);
$stmt = db()->prepare("SELECT permission_id FROM permission_role WHERE role_id = ?");
$stmt->execute([$role_id]);
$permisos_rol = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<form class="ajax-form mx-auto" action="guardar_permisos.php" method="post">
  <?php csrf_input(); ?>
  <input type="hidden" name="role_id" value="<?= $role_id ?>">
  <?php foreach ($permisos as $p): ?>
    <div class="form-check">
      <input type="checkbox" class="form-check-input" name="permisos[]" value="<?= $p['id'] ?>"
        <?= in_array($p['id'], $permisos_rol) ? 'checked' : '' ?>>
      <label class="form-check-label"><?= htmlspecialchars($p['label']) ?></label>
    </div>
  <?php endforeach; ?>
  <div class="d-flex justify-content-center mt-3">
    <button class="btn btn-success btn-sm">Guardar cambios</button>
  </div>
</form>
