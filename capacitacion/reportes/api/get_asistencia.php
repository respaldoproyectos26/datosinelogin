<?php
include_once '../../../includes/db.php';
$pdo = db();

$filtroCurso = $_GET['curso'] ?? '';
$fechaInicio = $_GET['inicio'] ?? '';
$fechaFin    = $_GET['fin'] ?? '';

$query = "SELECT * FROM capacitacion_asistencias WHERE 1=1";
$params = [];

if ($filtroCurso !== '') {
  $query .= " AND curso LIKE :curso";
  $params[':curso'] = "%$filtroCurso%";
}
if ($fechaInicio && $fechaFin) {
  $query .= " AND fecha BETWEEN :inicio AND :fin";
  $params[':inicio'] = $fechaInicio;
  $params[':fin'] = $fechaFin;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
