<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login(); csrf_check(); set_cache_headers('api');

$media_id=(int)($_POST['media_id']??0);
if($media_id<=0){ json(['ok'=>false,'error'=>'media_id']); }

$pdo=db();
$pdo->prepare("INSERT IGNORE INTO media_vistos (media_id,user_id) VALUES (?,?)")->execute([$media_id,current_user_id()]);
json(['ok'=>true]);
