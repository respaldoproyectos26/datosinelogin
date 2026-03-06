<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
header('Content-Type: application/json');

if (!auth()->can('eliminar_material')) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }

$id = (int)($_REQUEST['id'] ?? 0);
if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }

$pdo = db();
$it = $pdo->prepare("SELECT * FROM media WHERE id=?");
$it->execute([$id]);
$item = $it->fetch(PDO::FETCH_ASSOC);
if (!$item) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }

// borrar archivo si no es link
if ($item['tipo'] !== 'link') {
  $path = "../../../uploads/media/" . $item['archivo'];
  if ($item['archivo'] && file_exists($path)) @unlink($path);
}

// borrar registros relacionados
$pdo->prepare("DELETE FROM media_sesiones WHERE media_id=?")->execute([$id]);
$pdo->prepare("DELETE FROM media_vistos    WHERE media_id=?")->execute([$id]);

$pdo->prepare("DELETE FROM media WHERE id=?")->execute([$id]);

echo json_encode(['ok'=>true]);
