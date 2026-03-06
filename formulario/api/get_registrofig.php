<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
require_login();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET','POST'], true)) {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido'], JSON_UNESCAPED_UNICODE);
  exit;
}

$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $id = (int)($_GET['id'] ?? 0);
} else {
  $id = (int)($_POST['id'] ?? 0);
}

if ($id <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'ID inválido'], JSON_UNESCAPED_UNICODE);
  exit;
}

$pdo = db();

/*
  Ajusta tabla/campos reales. Aquí asumo:
  - tabla principal: registrorepmunicipal
  - quieres devolver también id_reglogin (para update)
*/
$st = $pdo->prepare("SELECT * FROM registrofig WHERE id = :id LIMIT 1");
$st->execute([':id' => $id]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  echo json_encode(['ok'=>false,'msg'=>'No encontrado'], JSON_UNESCAPED_UNICODE);
  exit;
}

echo json_encode(['ok'=>true,'data'=>$row], JSON_UNESCAPED_UNICODE);
