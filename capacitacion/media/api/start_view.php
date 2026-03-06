<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
header('Content-Type: application/json');
if (!is_logged_in()) { echo json_encode(['ok'=>false,'error'=>'No auth']); exit; }

$media_id = (int)($_POST['media_id'] ?? 0);
if ($media_id<=0) { echo json_encode(['ok'=>false,'error'=>'media_id']); exit; }

$pdo = db();
$stmt = $pdo->prepare("INSERT INTO media_sesiones (media_id,user_id,inicio,user_agent) VALUES (?,?,NOW(),?)");
$stmt->execute([$media_id, auth()->id(), substr($_SERVER['HTTP_USER_AGENT']??'',0,250)]);

echo json_encode(['ok'=>true,'session_id'=>$pdo->lastInsertId()]);
