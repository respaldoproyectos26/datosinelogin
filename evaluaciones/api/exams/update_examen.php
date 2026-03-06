<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
//require_once "../../../helpers/helpers_exams.php";
require_login();
if (!tiene_permiso('crear_evaluaciones')) denegar_acceso();

$exam_id = (int)($_POST['exam_id'] ?? 0);
if ($exam_id<=0) die("ID inválido");

$show_score = isset($_POST['show_score']) ? 1 : 0;
$show_correct_answers = isset($_POST['show_correct_answers']) ? 1 : 0;
$show_review = isset($_POST['show_review']) ? 1 : 0;
$show_detailed_feedback = isset($_POST['show_detailed_feedback']) ? 1 : 0;
$status = in_array($_POST['status'] ?? 'draft', ['draft','published','closed'], true) ? $_POST['status'] : 'draft';
$shuffle = isset($_POST['shuffle_questions']) ? 1 : 0;
$start_at = $_POST['start_at'] ? date('Y-m-d H:i:s', strtotime($_POST['start_at'])) : null;
$end_at   = $_POST['end_at']   ? date('Y-m-d H:i:s', strtotime($_POST['end_at']))   : null;

$st = db()->prepare("UPDATE exams
  SET show_score=?, show_correct_answers=?, show_review=?, show_detailed_feedback=?,
      status=?, start_at=?, end_at=?, shuffle_questions=?
  WHERE id=?");
$st->execute([$show_score,$show_correct_answers,$show_review,$show_detailed_feedback,
              $status,$start_at,$end_at,$shuffle,$exam_id]);

header("Location: ../editar.php?id=".$exam_id);
exit;
