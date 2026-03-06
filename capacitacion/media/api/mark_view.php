<?php
require_once '../../../helpers/db.php';
require_once '../../../helpers/auth.php';
require_once '../../../helpers/csrf.php';
require_login();

header('Content-Type: application/json');

$media_id = $_POST['media_id'] ?? null;
$user_id  = $_SESSION['user']['id'] ?? null;

if (!$media_id || !$user_id) {
  echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
  exit;
}

$stmt = db()->prepare("
  INSERT INTO materiales_vistos (user_id, media_id, fecha_visto)
  VALUES (?, ?, NOW())
  ON DUPLICATE KEY UPDATE fecha_visto = NOW()
");
$stmt->execute([$user_id, $media_id]);

echo json_encode(['success' => true, 'message' => 'Visualización registrada']);
