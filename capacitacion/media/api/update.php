<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
require_once "../../../helpers/helpers_media.php";
header('Content-Type: application/json');

if (!auth()->can('editar_material')) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }

$id = (int)($_POST['id'] ?? 0);
if ($id<=0) { echo json_encode(['ok'=>false,'error'=>'ID inválido']); exit; }

$pdo = db();
$it = $pdo->prepare("SELECT * FROM media WHERE id=?");
$it->execute([$id]);
$item = $it->fetch(PDO::FETCH_ASSOC);
if (!$item) { echo json_encode(['ok'=>false,'error'=>'No encontrado']); exit; }

$titulo = trim($_POST['titulo'] ?? '');
$descripcion = $_POST['descripcion'] ?? null;
$categoria = $_POST['categoria'] ?? null;
$link = trim($_POST['link'] ?? '');

$archivo = $item['archivo'];
$tipo    = $item['tipo'];

// si hay archivo nuevo
if (!empty($_FILES['archivo']['name'])) {
  $f = $_FILES['archivo'];
  $name = time().'_'.basename($f['name']);
  $dir = "../../../uploads/media";
  if (!is_dir($dir)) mkdir($dir,0777,true);
  if (!move_uploaded_file($f['tmp_name'], "$dir/$name")) {
    echo json_encode(['ok'=>false,'error'=>'Error subiendo archivo']); exit;
  }
  // borrar el anterior si no era link
  if ($tipo!=='link' && $archivo && file_exists("$dir/$archivo")) @unlink("$dir/$archivo");
  $archivo = $name;
  $tipo = $f['type'];
}

// si hay link (tiene prioridad sobre archivo)
if ($link !== '') {
  if (!media_validate_link($link)) { echo json_encode(['ok'=>false,'error'=>'Enlace no permitido']); exit; }
  // borrar archivo local anterior si existía
  $dir = "../../../uploads/media";
  if ($tipo!=='link' && $archivo && file_exists("$dir/$archivo")) @unlink("$dir/$archivo");
  $archivo = $link;
  $tipo = 'link';
}

$upd = $pdo->prepare("UPDATE media SET titulo=?, descripcion=?, archivo=?, tipo=?, categoria=? WHERE id=?");
$upd->execute([$titulo,$descripcion,$archivo,$tipo,$categoria,$id]);

echo json_encode(['ok'=>true]);
