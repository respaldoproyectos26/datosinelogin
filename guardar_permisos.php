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

$role_id = (int)($_POST['role_id'] ?? 0);
$permisos = $_POST['permisos'] ?? [];

try {
  $db = db();
  $db->beginTransaction();
  $db->prepare("DELETE FROM permission_role WHERE role_id = ?")->execute([$role_id]);
  $stmt = $db->prepare("INSERT INTO permission_role (role_id, permission_id) VALUES (?, ?)");
  foreach ($permisos as $pid) {
    $stmt->execute([$role_id, $pid]);
  }
  $db->commit();

  echo json_encode(['success' => true, 'message' => 'Permisos actualizados correctamente']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
