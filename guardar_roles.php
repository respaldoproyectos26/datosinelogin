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
csrf_check();

require_permission('ver_panel_roles');

$user_id = (int)($_POST['user_id'] ?? 0);
$roles = $_POST['roles'] ?? [];

try {
  $db = db();
  $db->beginTransaction();
  $db->prepare("DELETE FROM role_user WHERE user_id = ?")->execute([$user_id]);
  $stmt = $db->prepare("INSERT INTO role_user (user_id, role_id) VALUES (?, ?)");
  foreach ($roles as $rid) {
    $stmt->execute([$user_id, $rid]);
  }
  $db->commit();

  echo json_encode(['success' => true, 'message' => 'Roles actualizados correctamente']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
