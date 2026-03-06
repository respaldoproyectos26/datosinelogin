<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
//require_once "../../../helpers/helpers_exams.php";
require_login();
if (!tiene_permiso('asignar_evaluaciones')) denegar_acceso();

$exam_id = (int)($_POST['exam_id'] ?? 0);
if ($exam_id<=0) die("ID inválido");

$roles = $_POST['roles'] ?? [];
$users = $_POST['users'] ?? [];

// Limpiar asignaciones previas
$del = db()->prepare("DELETE FROM exam_assignments WHERE exam_id=?");
$del->execute([$exam_id]);

$ins = db()->prepare("INSERT INTO exam_assignments (exam_id, user_id, role_id) VALUES (?,?,?)");

// Insertar roles seleccionados
foreach ($roles as $rid) {
  $rid = (int)$rid;
  if ($rid>0) $ins->execute([$exam_id, null, $rid]);
}
// Insertar usuarios seleccionados
foreach ($users as $uid) {
  $uid = (int)$uid;
  if ($uid>0) $ins->execute([$exam_id, $uid, null]);
}

header("Location: ../../asignar.php?id=".$exam_id);
exit;
