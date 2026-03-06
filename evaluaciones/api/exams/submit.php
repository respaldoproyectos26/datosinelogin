<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
//require_once "../../../helpers/helpers_exams.php";
require_login();
header('Content-Type: application/json');

if (!tiene_permiso('presentar_evaluaciones')) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }

$attemptId = (int)($_POST['attempt_id'] ?? 0);
if ($attemptId<=0){ echo json_encode(['ok'=>false,'error'=>'attempt_id inválido']); exit; }

$pdo = db();
$u = $_SESSION['user'] ?? [];
$userId = (int)($u['id'] ?? 0);

// Cargar intento + examen
$st = $pdo->prepare("SELECT a.id,a.exam_id,a.user_id,a.started_at,e.time_limit_min,e.show_score,e.show_correct_answers,e.show_review,e.show_detailed_feedback
                     FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE a.id=?");
$st->execute([$attemptId]);
$att = $st->fetch(PDO::FETCH_ASSOC);
if(!$att){ echo json_encode(['ok'=>false,'error'=>'Intento no existe']); exit; }
if((int)$att['user_id']!==$userId){ echo json_encode(['ok'=>false,'error'=>'No es tu intento']); exit; }

// Evitar doble envío
$chk = $pdo->prepare("SELECT submitted_at FROM attempts WHERE id=?");
$chk->execute([$attemptId]);
if($chk->fetchColumn()){ echo json_encode(['ok'=>false,'error'=>'Intento ya enviado']); exit; }

// Traer preguntas
$qst = $pdo->prepare("SELECT id,type,points FROM questions WHERE exam_id=?");
$qst->execute([$att['exam_id']]);
$qs = $qst->fetchAll(PDO::FETCH_ASSOC);
$map = [];
foreach($qs as $q){ $map[(int)$q['id']]=$q; }

$pdo->beginTransaction();
try{
  $ins = $pdo->prepare("INSERT INTO answers (attempt_id,question_id,option_id,text_answer)
                        VALUES (?,?,?,?)
                        ON DUPLICATE KEY UPDATE option_id=VALUES(option_id), text_answer=VALUES(text_answer)");
  $score = 0.0;

  foreach($map as $qid=>$q){
    $optId = null; $text=null;
    if($q['type']==='text'){
      $text = trim((string)($_POST['t_'.$qid] ?? ''));
    } else {
      $tmp = (int)($_POST['q_'.$qid] ?? 0);
      $optId = $tmp>0 ? $tmp : null;
    }
    $ins->execute([$attemptId,$qid,$optId,$text]);

    if ($q['type']!=='text' && $optId){
      $ck = $pdo->prepare("SELECT is_correct FROM options WHERE id=? AND question_id=?");
      $ck->execute([$optId,$qid]);
      if ((int)$ck->fetchColumn()===1) $score += (float)$q['points'];
    }
  }

  // Duración
  $startedAt = new DateTimeImmutable($att['started_at']);
  $submittedAt = new DateTimeImmutable('now');
  $duration = max(0, $submittedAt->getTimestamp() - $startedAt->getTimestamp());

  $up = $pdo->prepare("UPDATE attempts SET submitted_at=NOW(), duration_sec=?, score=? WHERE id=?");
  $up->execute([$duration,$score,$attemptId]);

  $pdo->commit();

  echo json_encode(['ok'=>true,'attempt_id'=>$attemptId,'score'=>$score,
    'show_score'=>(int)$att['show_score'],
    'show_correct_answers'=>(int)$att['show_correct_answers'],
    'show_review'=>(int)$att['show_review'],
    'show_detailed_feedback'=>(int)$att['show_detailed_feedback']
  ]);
}catch(Throwable $th){
  $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>'No se pudo enviar']);
}
