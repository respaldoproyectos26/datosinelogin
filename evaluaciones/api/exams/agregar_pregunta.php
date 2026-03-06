<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
//require_once "../../../helpers/helpers_exams.php";
require_login();
if (!tiene_permiso('crear_evaluaciones')) denegar_acceso();

$exam_id = (int)($_POST['exam_id'] ?? 0);
$type    = $_POST['type'] ?? '';
$stem    = trim($_POST['stem'] ?? '');
$options = $_POST['options'] ?? [];
$correct = trim($_POST['correct'] ?? '');
if ($exam_id<=0 || !$type || !$stem) die("Datos incompletos");

$media_id = null;
if (!empty($_FILES['media']['tmp_name'])) {
  @mkdir(__DIR__."/../../uploads/exam_media", 0775, true);
  $name = time() . "_" . preg_replace('/[^\w\.-]/','_', $_FILES['media']['name']);
  $dest = __DIR__."/../../uploads/exam_media/".$name;
  if (move_uploaded_file($_FILES['media']['tmp_name'], $dest)) {
    $mime = mime_content_type($dest) ?: null;
    $size = filesize($dest) ?: null;
    $insm = db()->prepare("INSERT INTO exam_media (file,mime,size_bytes) VALUES (?,?,?)");
    $insm->execute([$name,$mime,$size]);
    $media_id = db()->lastInsertId();
  }
}

$insq = db()->prepare("INSERT INTO questions (exam_id,type,stem,media_id) VALUES (?,?,?,?)");
$insq->execute([$exam_id,$type,$stem,$media_id]);
$qid = (int)db()->lastInsertId();

if ($type==='mcq') {
  foreach ($options as $opt) {
    $txt = trim($opt);
    if ($txt==='') continue;
    $is_correct = ($txt === $correct) ? 1 : 0;
    db()->prepare("INSERT INTO options (question_id,label,is_correct) VALUES (?,?,?)")
      ->execute([$qid,$txt,$is_correct]);
  }
} elseif ($type==='truefalse') {
  // Generamos dos opciones (V/F) y marcamos la correcta según $correct
  $c = strtoupper($correct)==='V' ? 'V' : 'F';
  db()->prepare("INSERT INTO options (question_id,label,is_correct) VALUES (?,?,?)")->execute([$qid,'V', $c==='V'?1:0]);
  db()->prepare("INSERT INTO options (question_id,label,is_correct) VALUES (?,?,?)")->execute([$qid,'F', $c==='F'?1:0]);
}

header("Location: ../../editar.php?id=".$exam_id);
exit;
