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

$role_id = $_POST['role_id'];
$permisos = $_POST['permisos'] ?? [];

// Limpiar anteriores
$stmt = db()->prepare("DELETE FROM permission_role WHERE role_id = ?");
$stmt->execute([$role_id]);

foreach ($permisos as $perm_id) {
    db()->prepare("INSERT INTO permission_role (permission_id, role_id) VALUES (?, ?)")->execute([$perm_id, $role_id]);
}

header('Location: gestion_roles.php');
exit;
