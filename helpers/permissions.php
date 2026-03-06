<?php
function load_roles_permissions_to_session($user_id): void {
  $pdo = db();

  $st = $pdo->prepare("
    SELECT r.name FROM roles r
    JOIN role_user ru ON ru.role_id = r.id
    WHERE ru.user_id = :uid
  ");
  $st->execute([':uid'=>$user_id]);
  $_SESSION['roles'] = array_column($st->fetchAll(), 'name');

  $st = $pdo->prepare("
    SELECT DISTINCT p.name
    FROM permissions p
    JOIN permission_role pr ON pr.permission_id = p.id
    JOIN role_user ru ON ru.role_id = pr.role_id
    WHERE ru.user_id = :uid
  ");
  $st->execute([':uid'=>$user_id]);
  $_SESSION['permissions'] = array_column($st->fetchAll(), 'name');
}

function hasRole($role): bool { return in_array($role, $_SESSION['roles'] ?? [], true); }
function can($perm): bool     { return in_array($perm, $_SESSION['permissions'] ?? [], true); }

function save_log($user_id, $action, $ip, $msg = null): void {
  if (!$user_id) { error_log("log sin user_id: $action @$ip"); return; }
  $st = db()->prepare("INSERT INTO logs (user_id, action, timestamp, ip_address, details)
                       VALUES (:u,:a,NOW(),:ip,:d)");
  $st->execute([':u'=>$user_id, ':a'=>$action, ':ip'=>$ip, ':d'=>$msg]);
}

function require_permission($permiso): void {
  if (!isset($_SESSION['user'])) { require_login(); return; }
  if (can($permiso)) return;

  save_log(current_user_id(), "Sin permiso: $permiso", $_SERVER['REMOTE_ADDR'] ?? '-', 'Acceso denegado');
  http_response_code(403);
  if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
    echo json_encode(['ok'=>false,'msg'=>'Acceso denegado']); exit;
  }
  exit('Acceso denegado');
}
