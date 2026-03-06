<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login(); set_cache_headers('api');

$session_id = (int)($_POST['session_id'] ?? 0);
$elapsed    = max(0, (int)($_POST['elapsed'] ?? 0));
if ($session_id<=0) exit;

$st = db()->prepare("UPDATE media_sesiones SET fin=NOW(), duracion_sec=? WHERE id=? AND user_id=?");
$st->execute([$elapsed, $session_id, current_user_id()]);
