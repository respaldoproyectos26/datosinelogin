<?php
require_once '../helpers/auth.php';
require_once '../helpers/csrf.php';
require_once '../helpers/db.php';
require_once '../helpers/response.php';

require_login();
csrf_check();
require_permission('eliminar');

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['id'])) {
    json(['ok' => false, 'error' => 'ID requerido']);
}

$stmt = db()->prepare("DELETE FROM personas WHERE id = ?");
$ok = $stmt->execute([$data['id']]);

json(['ok' => $ok]);
