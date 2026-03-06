<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login(); csrf_check(); set_cache_headers('api');

$media_id = (int)($_POST['media_id'] ?? 0);
if ($media_id<=0) { echo json_encode(['ok'=>false,'error'=>'media_id']); exit; }

$st = db()->prepare("INSERT INTO media_sesiones (media_id,user_id,inicio,user_agent) VALUES (?,?,NOW(),?)");
$st->execute([$media_id, current_user_id(), substr($_SERVER['HTTP_USER_AGENT']??'',0,250)]);
echo json_encode(['ok'=>true,'session_id'=>db()->lastInsertId()]);
