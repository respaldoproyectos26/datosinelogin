<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";

$session_id = (int)($_POST['session_id'] ?? 0);
$elapsed    = max(0, (int)($_POST['elapsed'] ?? 0));
if ($session_id<=0) exit;

$pdo = db();
$upd = $pdo->prepare("
  UPDATE media_sesiones
  SET fin = NOW(), duracion_sec = ?
  WHERE id = ? AND user_id = ?
");
$upd->execute([$elapsed, $session_id, auth()->id()]);
