<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('api');

require_login();
csrf_check();
// require_permission('eliminar_usuarios');

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) json(['success'=>false,'message'=>'ID inválido'], 422);

// Seguridad: evita borrar al admin (id=1) si quieres:
if ($id === 1) json(['success'=>false,'message'=>'No se puede borrar admin'], 403);

try {
  db()->prepare("DELETE FROM role_user WHERE user_id = ?")->execute([$id]);
  db()->prepare("DELETE FROM ine_usuarios_sanluis WHERE id=?")->execute([$id]);
  json(['success'=>true,'message'=>'Usuario eliminado']);
} catch (Throwable $e) {
  error_log("usuarios delete error: ".$e->getMessage());
  json(['success'=>false,'message'=>'Error al eliminar'], 500);
}
