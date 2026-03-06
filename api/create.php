<?php
require_once '../helpers/auth.php';
require_once '../helpers/csrf.php';
require_once '../helpers/db.php';
require_once '../helpers/response.php';

require_login();
csrf_check();
require_permission('crear');

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['nombre']) || empty($data['curp'])) {
    json(['ok' => false, 'error' => 'Faltan datos obligatorios']);
}

$stmt = db()->prepare("INSERT INTO personas (nombre, curp, creado_por) VALUES (?, ?, ?)");
$ok = $stmt->execute([
    $data['nombre'],
    $data['curp'],
    #current_user_id()
]);

json(['ok' => $ok]);
