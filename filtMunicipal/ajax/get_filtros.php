<?php
require_once '../../helpers/errores.php';
require_once '../../helpers/db.php';
require_once '../../helpers/auth.php';

header('Content-Type: application/json');

// Validar el parámetro 'filtro'
if (!isset($_GET['filtro']) || empty($_GET['filtro'])) {
    echo json_encode(["error" => "Parámetro 'filtro' faltante o vacío"]);
    exit;
}

$filtro = $_GET['filtro'];
$columnas_permitidas = ['nombre', 'paterno', 'materno', 'estado', 'municalc', 'seccion'];

if (!in_array($filtro, $columnas_permitidas)) {
    echo json_encode(["error" => "Filtro inválido"]);
    exit;
}

if ($filtro === 'seccion') {
  $sql = "SELECT DISTINCT seccion FROM registrorepmunicipal WHERE seccion IS NOT NULL 
          ORDER BY CAST(seccion AS UNSIGNED), seccion";
} else {
  $sql = "SELECT DISTINCT $filtro FROM registrorepmunicipal 
          WHERE $filtro IS NOT NULL AND $filtro <> ''
          ORDER BY $filtro ASC";
}

try {
    $db = db();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $valores = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // ⚠️ Solo columna $filtro
    echo json_encode($valores, JSON_PRETTY_PRINT);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error en base de datos: " . $e->getMessage()]);
}
