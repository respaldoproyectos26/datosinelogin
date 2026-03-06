<?php
require_once "../../helpers/db.php";
require_once "../../helpers/auth.php";
//require_once "../../helpers/helpers_exams.php";
require_login();
header('Content-Type: application/json; charset=utf-8');

if (!tiene_permiso('ver_resultados_evaluaciones')) { echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit; }

$pdo = db();

// Parámetros DataTables
$draw  = (int)($_POST['draw']  ?? 1);
$start = (int)($_POST['start'] ?? 0);
$len   = min(100, max(10,(int)($_POST['length'] ?? 25)));
$search= trim((string)($_POST['search'] ?? ''));

// Filtros
$examId = (int)($_POST['exam_id'] ?? 0);
$from   = trim((string)($_POST['from'] ?? ''));
$to     = trim((string)($_POST['to'] ?? ''));

$where = ["a.submitted_at IS NOT NULL"];
$params = [];

if ($examId > 0) { $where[] = "a.exam_id = ?"; $params[] = $examId; }
if ($from !== '') { $where[] = "DATE(a.submitted_at) >= ?"; $params[] = $from; }
if ($to   !== '') { $where[] = "DATE(a.submitted_at) <= ?"; $params[] = $to; }

if ($search !== '') {
  $where[] = "(e.title LIKE ? OR u.usuario LIKE ?)";
  $params[] = "%$search%";
  $params[] = "%$search%";
}

$sqlWhere = $where ? ("WHERE ".implode(" AND ", $where)) : "";

// Orden
$cols = ['a.submitted_at','e.title','u.usuario','a.score','total_points','a.duration_sec'];
$orderCol = (int)($_POST['order_col'] ?? 0);
$orderDir = ($_POST['order_dir'] ?? 'desc')==='asc' ? 'ASC' : 'DESC';
$orderBy = $cols[$orderCol] ?? 'a.submitted_at';

// Total
$total = (int)$pdo->query("SELECT COUNT(*) FROM attempts a WHERE a.submitted_at IS NOT NULL")->fetchColumn();

// Total filtrado
$stmtCnt = $pdo->prepare("
  SELECT COUNT(*)
  FROM attempts a
  JOIN exams e ON e.id=a.exam_id
  JOIN ine_usuarios u ON u.id=a.user_id
  $sqlWhere
");
$stmtCnt->execute($params);
$filtered = (int)$stmtCnt->fetchColumn();

// KPIs
$stmtK = $pdo->prepare("
  SELECT COUNT(*) total,
         COALESCE(AVG(a.score),0) prom,
         COALESCE(MAX(a.score),0) max,
         COALESCE(MIN(a.score),0) min
  FROM attempts a
  JOIN exams e ON e.id=a.exam_id
  JOIN ine_usuarios u ON u.id=a.user_id
  $sqlWhere
");
$stmtK->execute($params);
$kpis = $stmtK->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'prom'=>0,'max'=>0,'min'=>0];

// Data
$stmt = $pdo->prepare("
  SELECT a.id as attempt_id, a.submitted_at, a.duration_sec, a.score,
         e.title AS exam_title,
         u.usuario,
         (
           SELECT COALESCE(SUM(points),0)
           FROM questions q WHERE q.exam_id = a.exam_id
         ) AS total_points
  FROM attempts a
  JOIN exams e ON e.id=a.exam_id
  JOIN ine_usuarios u ON u.id=a.user_id
  $sqlWhere
  ORDER BY $orderBy $orderDir
  LIMIT $len OFFSET $start
");
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'ok'=> true,
  'draw'=> $draw,
  'recordsTotal'=> $total,
  'recordsFiltered'=> $filtered,
  'data'=> $rows,
  'kpis'=> [
    'total'=> (int)$kpis['total'],
    'prom' => (float)$kpis['prom'],
    'max'  => (float)$kpis['max'],
    'min'  => (float)$kpis['min'],
  ],
]);
