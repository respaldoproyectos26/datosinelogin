<?php
require_once '../../helpers/errores.php';
require_once '../../helpers/db.php';
require_once '../../helpers/auth.php';

header('Content-Type: application/json');

// Evita cualquier salida antes del JSON
ob_start();

$nombre    = isset($_POST['nombre'])    ? $_POST['nombre']    : '';
$paterno  = isset($_POST['paterno'])   ? $_POST['paterno']   : '';
$materno  = isset($_POST['materno'])   ? $_POST['materno']   : '';
$estado   = isset($_POST['estado'])    ? $_POST['estado']    : '';
$municalc = isset($_POST['municalc'])  ? $_POST['municalc']  : '';
$seccion  = isset($_POST['seccion'])   ? $_POST['seccion']   : '';

$sql = "SELECT id, nombre, paterno, materno, estado, municalc, seccion, foto FROM registrofig3 WHERE 1=1";
$params = [];

if ($nombre !== '') {
    $sql .= " AND nombre = ?";
    $params[] = $nombre;
}
if ($paterno !== '') {
    $sql .= " AND paterno = ?";
    $params[] = $paterno;
}
if ($materno !== '') {
    $sql .= " AND materno = ?";
    $params[] = $materno;
}
if ($estado !== '') {
    $sql .= " AND estado = ?";
    $params[] = $estado;
}
if ($municalc !== '') {
    $sql .= " AND municalc = ?";
    $params[] = $municalc;
}
if ($seccion !== '') {
    $sql .= " AND seccion = ?";
    $params[] = $seccion;
}

try {
    $db = db();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll();
} catch (PDOException $e) {
    http_response_code(500);
    ob_end_clean();
    echo json_encode(["error" => "Error en base de datos: " . $e->getMessage()]);
    exit;
}

ob_end_clean();
echo json_encode(["data" => $data], JSON_PRETTY_PRINT);
