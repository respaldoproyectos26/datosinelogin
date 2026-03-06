<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
//require_once "../../../helpers/helpers_exams.php";
require_login();

if (!tiene_permiso('crear_evaluaciones')) {
    denegar_acceso();
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$time = (int)($_POST['time_limit_min'] ?? 0);
$attempts = max(1, (int)($_POST['attempts_allowed'] ?? 1));
$start_at = $_POST['start_at'] ? date('Y-m-d H:i:s', strtotime($_POST['start_at'])) : null;
$end_at   = $_POST['end_at']   ? date('Y-m-d H:i:s', strtotime($_POST['end_at']))   : null;
$shuffle  = isset($_POST['shuffle_questions']) ? 1 : 0;

$stmt = db()->prepare("
  INSERT INTO exams (title, description, time_limit_min, attempts_allowed, start_at, end_at, shuffle_questions, status, created_by)
  VALUES (?,?,?,?,?,?,?,?,?)
");
$stmt->execute([$title,$description,$time,$attempts,$start_at,$end_at,$shuffle,'draft',$_SESSION['user']['id']]);


header("Location: ../../index.php");
exit;
