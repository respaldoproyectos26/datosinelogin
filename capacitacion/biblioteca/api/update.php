<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login(); csrf_check(); set_cache_headers('api');
if (!can('editar_material')) { json(['ok'=>false,'error'=>'Sin permiso']); }

$id=(int)($_POST['id']??0);
if($id<=0){ json(['ok'=>false,'error'=>'ID inválido']); }

$pdo=db();
$it=$pdo->prepare("SELECT * FROM media WHERE id=?"); $it->execute([$id]);
$media=$it->fetch(PDO::FETCH_ASSOC);
if(!$media){ json(['ok'=>false,'error'=>'Material no encontrado']); }

$titulo=trim($_POST['titulo']??'');
$descripcion=trim($_POST['descripcion']??'');
$categoria=trim($_POST['categoria']??'');

$archivo=$media['archivo']; $tipo=$media['tipo'];
$max=20*1024*1024;

if(!empty($_FILES['archivo']['name'])){
  if($_FILES['archivo']['error']!==UPLOAD_ERR_OK){ json(['ok'=>false,'error'=>'Error de subida']); }
  if($_FILES['archivo']['size']>$max){ json(['ok'=>false,'error'=>'Máximo 20MB']); }

  $mime=mime_content_type($_FILES['archivo']['tmp_name'])?:$_FILES['archivo']['type'];
  $dirType=(str_starts_with($mime,'image')?'imagenes':(str_starts_with($mime,'video')?'videos':($mime==='application/pdf'?'pdf':'otros')));
  $dir=__DIR__."/../uploads/$dirType";
  if(!is_dir($dir)) mkdir($dir,0777,true);

  $name=time().'_'.preg_replace('~[^A-Za-z0-9._-]~','_',$_FILES['archivo']['name']);
  move_uploaded_file($_FILES['archivo']['tmp_name'],"$dir/$name");
  $archivo="$dirType/$name"; $tipo=$mime;
}

$st=$pdo->prepare("UPDATE media SET titulo=?,descripcion=?,categoria=?,archivo=?,tipo=? WHERE id=?");
$st->execute([$titulo,$descripcion,$categoria,$archivo,$tipo,$id]);
json(['ok'=>true]);
