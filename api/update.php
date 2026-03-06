<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/errores_api.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/cache_headers.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/permissions.php';

require_login();
set_cache_headers('api');
csrf_check();
# require_permission('editar');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['id']) || empty($data['nombre']) || empty($data['curp'])) {
    json(['ok' => false, 'error' => 'Faltan datos']);
}

$stmt = db()->prepare("UPDATE personas SET nombre = ?, curp = ? WHERE id = ?");
$ok = $stmt->execute([
    $data['nombre'],
    $data['curp'],
    $data['id']
]);

json(['ok' => $ok]);
