<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_once __DIR__ . '/../../../helpers/helpers_media.php';
require_login(); csrf_check(); set_cache_headers('api');

if (!can('subir_material')) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }

$titulo = trim($_POST['titulo']??'');
$descripcion = trim($_POST['descripcion']??'');
$categoria = trim($_POST['categoria']??'');
$link = trim($_POST['link']??'');

if ($titulo===''){ echo json_encode(['ok'=>false,'error'=>'Título requerido']); exit; }

$maxBytes = 20 * 1024 * 1024; // 20MB
$archivo = null; $tipo = null;

function targetDirForMime($mime){
  if (str_starts_with($mime,'image')) return 'imagenes';
  if (str_starts_with($mime,'video')) return 'videos';
  if ($mime==='application/pdf') return 'pdf';
  return 'otros';
}

if (!empty($_FILES['archivo']['name'])) {
  if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) { echo json_encode(['ok'=>false,'error'=>'Error de subida']); exit; }
  if ($_FILES['archivo']['size'] > $maxBytes) { echo json_encode(['ok'=>false,'error'=>'Máximo 20MB']); exit; }

  $mime = mime_content_type($_FILES['archivo']['tmp_name']) ?: $_FILES['archivo']['type'];
  $dirType = targetDirForMime($mime);
  $dir = __DIR__ . "/../uploads/$dirType";
  if (!is_dir($dir)) mkdir($dir,0777,true);

  $name = time().'_'.preg_replace('~[^A-Za-z0-9._-]~','_', $_FILES['archivo']['name']);
  if (!move_uploaded_file($_FILES['archivo']['tmp_name'], "$dir/$name")) {
    echo json_encode(['ok'=>false,'error'=>'No se pudo guardar el archivo']); exit;
  }
  $archivo = "$dirType/$name";   // relativo a /uploads
  $tipo = $mime;
}

if ($link) {
  if (!media_validate_link($link)) { echo json_encode(['ok'=>false,'error'=>'Enlace no permitido']); exit; }
  if ($archivo) { echo json_encode(['ok'=>false,'error'=>'Usa archivo O link, no ambos']); exit; }
  $archivo = $link;
  $tipo = 'link';
}

if (!$archivo) { echo json_encode(['ok'=>false,'error'=>'Debes subir archivo o link']); exit; }

$st = db()->prepare("INSERT INTO media (titulo,descripcion,archivo,tipo,categoria,creado_por) VALUES (?,?,?,?,?,?)");
$st->execute([$titulo,$descripcion,$archivo,$tipo,$categoria, current_user_id()]);
echo json_encode(['ok'=>true]);
