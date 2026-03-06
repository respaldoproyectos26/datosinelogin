<?php
require_once '../../helpers/errores.php';
require_once '../../helpers/db.php';
require_once '../../helpers/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Método no permitido']);
    exit;
}

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($id <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido']);
    exit;
}

// Opcional: validar que el registro existe antes de eliminar
try {
    $db = db();
    $stmt = $db->prepare("DELETE FROM registrorepdistfed WHERE id = ?");
    $success = $stmt->execute([$id]);
    if ($success) {
    echo json_encode(['ok' => true]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'No se pudo eliminar']);
    exit;
}
