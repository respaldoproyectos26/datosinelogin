<?php
// datosinelogin/formulario/api/get_secciones.php
header('Content-Type: application/json; charset=utf-8');

require_once '../../helpers/auth.php';
require_once '../../helpers/db.php';
require_once '../../helpers/errores.php';

try {
  if (empty($_POST['idmunicalc'])) {
    echo json_encode(['error' => 'Municipio requerido']); exit;
  }

  $muni = trim($_POST['idmunicalc']);
  $pdo = db();

  $stmt = $pdo->prepare("SELECT DISTINCT seccion FROM secciones WHERE municalc = :m ORDER BY CAST(seccion AS UNSIGNED), seccion");
  $stmt->execute([':m' => $muni]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($rows ?: []);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en la base de datos', 'detail' => $e->getMessage()]);
}
