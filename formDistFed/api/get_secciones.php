<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
require_login();

$muni = trim((string)($_POST['idmunicalc'] ?? ''));
if ($muni === '') { echo json_encode(['error' => 'Municipio requerido']); exit; }

try {
  $pdo = db();
  $sql = "SELECT DISTINCT seccion
          FROM secciones
          WHERE municalc = :m
          ORDER BY (seccion + 0), seccion";
  $st = $pdo->prepare($sql);
  $st->bindValue(':m', $muni, PDO::PARAM_STR);
  $st->execute();
  echo json_encode($st->fetchAll(PDO::FETCH_ASSOC) ?: []);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en la base de datos']);
}