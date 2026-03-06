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

$user_id = $_POST['user_id'];
$roles = $_POST['roles'] ?? [];

// Limpiar anteriores
$stmt = db()->prepare("DELETE FROM role_user WHERE user_id = ?");
$stmt->execute([$user_id]);

foreach ($roles as $role_id) {
    db()->prepare("INSERT INTO role_user (user_id, role_id) VALUES (?, ?)")->execute([$user_id, $role_id]);
}

header('Location: gestion_roles.php');
exit;
