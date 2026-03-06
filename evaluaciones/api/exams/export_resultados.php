<?php
require_once "../../helpers/db.php";
require_once "../../helpers/auth.php";
require_login();
if (!tiene_permiso('ver_resultados_evaluaciones')) { http_response_code(403); exit; }

$pdo = db();
$examId = (int)($_GET['exam_id'] ?? 0);
$from   = trim((string)($_GET['from'] ?? ''));
$to     = trim((string)($_GET['to'] ?? ''));

$where=[]; $params=[];
$where[] = "a.submitted_at IS NOT NULL";
if ($examId>0){ $where[]="a.exam_id=?"; $params[]=$examId; }
if ($from!==''){ $where[]="DATE(a.submitted_at)>=?"; $params[]=$from; }
if ($to  !==''){ $where[]="DATE(a.submitted_at)<=?"; $params[]=$to; }
$sqlWhere = $where? "WHERE ".implode(" AND ",$where) : "";

$stmt = $pdo->prepare("
  SELECT a.id as attempt_id, a.submitted_at, a.duration_sec, a.score,
         e.title AS exam_title, u.usuario,
         (SELECT COALESCE(SUM(points),0) FROM questions q WHERE q.exam_id=a.exam_id) AS total_points
  FROM attempts a
  JOIN exams e ON e.id=a.exam_id
  JOIN ine_usuarios u ON u.id=a.user_id
  $sqlWhere
  ORDER BY a.submitted_at DESC
");
$stmt->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="resultados_examenes.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['attempt_id','fecha','examen','usuario','puntaje','total','duracion_seg']);

while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
  fputcsv($out, [
    $r['attempt_id'],
    $r['submitted_at'],
    $r['exam_title'],
    $r['usuario'],
    $r['score'],
    $r['total_points'],
    $r['duration_sec']
  ]);
}
fclose($out);
