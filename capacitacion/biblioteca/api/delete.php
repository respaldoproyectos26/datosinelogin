<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login(); csrf_check(); set_cache_headers('api');
if (!can('eliminar_material')) { json(['ok'=>false,'error'=>'Sin permiso']); }

$id=(int)($_POST['id']??0);
if($id<=0){ json(['ok'=>false,'error'=>'ID inválido']); }

$pdo=db();
$it=$pdo->prepare("SELECT * FROM media WHERE id=?"); $it->execute([$id]);
$row=$it->fetch(PDO::FETCH_ASSOC);
if(!$row){ json(['ok'=>false,'error'=>'No encontrado']); }

if($row['tipo']!=='link'){
  $path=__DIR__."/../uploads/{$row['archivo']}";
  if(is_file($path)) unlink($path);
}

$pdo->prepare("DELETE FROM media WHERE id=?")->execute([$id]);
json(['ok'=>true]);
