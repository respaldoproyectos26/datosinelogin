<?php
// datosinelogin/formulario/api/get_municipios.php
header('Content-Type: application/json; charset=utf-8');

require_once '../../helpers/auth.php';
require_once '../../helpers/db.php';
require_once '../../helpers/errores.php';

try {
  if (empty($_POST['idedo'])) {
    echo json_encode(['error' => 'Estado requerido']); exit;
  }

  $idedo = trim($_POST['idedo']);
  $pdo = db();

  // municalc pertenece al estado (nomestado)
  $stmt = $pdo->prepare("SELECT municalc FROM municalc WHERE nomestado = :edo ORDER BY municalc");
  $stmt->execute([':edo' => $idedo]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($rows ?: []);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en la base de datos', 'detail' => $e->getMessage()]);
}
