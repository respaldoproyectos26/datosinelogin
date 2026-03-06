<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
//require_once "../../../helpers/helpers_exams.php";
require_login();
header('Content-Type: application/json');

if (!tiene_permiso('presentar_evaluaciones')) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }

$examId = (int)($_POST['exam_id'] ?? 0);
if ($examId<=0){ echo json_encode(['ok'=>false,'error'=>'exam_id inválido']); exit; }

$pdo = db();
$u = $_SESSION['user'] ?? [];
$userId = (int)($u['id'] ?? 0);

// Cargar examen
$st = $pdo->prepare("SELECT * FROM exams WHERE id=?");
$st->execute([$examId]);
$exam = $st->fetch(PDO::FETCH_ASSOC);
if(!$exam){ echo json_encode(['ok'=>false,'error'=>'Examen no encontrado']); exit; }
if($exam['status']!=='published'){ echo json_encode(['ok'=>false,'error'=>'El examen no está publicado']); exit; }

// Validar ventana
$now = new DateTimeImmutable('now');
if(!empty($exam['start_at']) && $now < new DateTimeImmutable($exam['start_at'])){
  echo json_encode(['ok'=>false,'error'=>'Aún no inicia']); exit;
}
if(!empty($exam['end_at']) && $now > new DateTimeImmutable($exam['end_at'])){
  echo json_encode(['ok'=>false,'error'=>'El examen ya cerró']); exit;
}

// Validar asignación por usuario o por rol
$rolesUser = $pdo->prepare("SELECT role_id FROM role_user WHERE user_id=?");
$rolesUser->execute([$userId]);
$roleIds = array_column($rolesUser->fetchAll(PDO::FETCH_ASSOC), 'role_id');

$qAsg = "SELECT 1 FROM exam_assignments WHERE exam_id=? AND (user_id=? ";
$params = [$examId, $userId];
if ($roleIds) {
  $in = implode(',', array_fill(0, count($roleIds), '?'));
  $qAsg .= " OR role_id IN ($in)";
  $params = array_merge($params, $roleIds);
}
$qAsg .= ") LIMIT 1";
$chk = $pdo->prepare($qAsg);
$chk->execute($params);
if(!$chk->fetchColumn()){
  echo json_encode(['ok'=>false,'error'=>'No asignado a este examen']); exit;
}

// Validar intentos previos
$allowed = (int)$exam['attempts_allowed'];
$cnt = $pdo->prepare("SELECT COUNT(*) FROM attempts WHERE exam_id=? AND user_id=? AND submitted_at IS NOT NULL");
$cnt->execute([$examId,$userId]);
if((int)$cnt->fetchColumn() >= $allowed){
  echo json_encode(['ok'=>false,'error'=>'Intentos agotados']); exit;
}

// Crear intento + cargar preguntas
$pdo->beginTransaction();
try{
  $ins = $pdo->prepare("INSERT INTO attempts (exam_id,user_id) VALUES (?,?)");
  $ins->execute([$examId,$userId]);
  $attemptId = (int)$pdo->lastInsertId();

  $qs = $pdo->prepare("SELECT id,type,stem,points,media_id FROM questions WHERE exam_id=?");
  $qs->execute([$examId]);
  $questions = $qs->fetchAll(PDO::FETCH_ASSOC);

  // Opciones y media
  $opt = $pdo->prepare("SELECT id,label FROM options WHERE question_id=? ORDER BY id ASC");
  $media = $pdo->prepare("SELECT file,mime FROM exam_media WHERE id=?");
  $shuffle = ((int)$exam['shuffle_questions']===1);

  if($shuffle) shuffle($questions);

  $payload = [];
  foreach($questions as $q){
    $opts=[];
    if($q['type']!=='text'){
      $opt->execute([$q['id']]);
      $opts = $opt->fetchAll(PDO::FETCH_ASSOC);
      if($shuffle) shuffle($opts);
    }
    $murl = null; $mt = null;
    if(!empty($q['media_id'])){
      $media->execute([$q['media_id']]);
      if ($mm = $media->fetch(PDO::FETCH_ASSOC)) {
        $murl = "../../uploads/exam_media/".$mm['file'];
        $mt = $mm['mime'];
      }
    }
    $payload[] = [
      'id'=>(int)$q['id'],
      'type'=>$q['type'],
      'stem'=>$q['stem'],
      'points'=>(float)$q['points'],
      'options'=>$opts,
      'media_url'=>$murl,
      'media_type'=>$mt
    ];
  }

  $pdo->commit();
  echo json_encode([
    'ok'=>true,
    'attempt_id'=>$attemptId,
    'exam'=>['id'=>(int)$exam['id'],'title'=>$exam['title'],'time_limit_min'=>(int)$exam['time_limit_min']],
    'questions'=>$payload
  ]);
}catch(Throwable $th){
  $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>'No se pudo iniciar']);
}
