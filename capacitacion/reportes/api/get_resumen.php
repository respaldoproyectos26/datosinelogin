<?php
// capacitacion/reportes/api/get_resumen.php
include_once '../../../includes/db.php';
$pdo = db();

$filtroCurso = $_GET['curso'] ?? '';
$fechaInicio = $_GET['inicio'] ?? '';
$fechaFin    = $_GET['fin'] ?? '';

$where = " WHERE 1=1 ";
$params = [];

if ($filtroCurso !== '') {
  $where .= " AND curso LIKE :curso";
  $params[':curso'] = "%$filtroCurso%";
}
if ($fechaInicio && $fechaFin) {
  $where .= " AND fecha BETWEEN :inicio AND :fin";
  $params[':inicio'] = $fechaInicio;
  $params[':fin']    = $fechaFin;
}

/* KPIs globales */
$sqlKpi = "
  SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN asistencia='ASISTIÓ' THEN 1 ELSE 0 END) AS asistieron,
    SUM(CASE WHEN asistencia='NO ASISTIÓ' THEN 1 ELSE 0 END) AS no_asistieron,
    ROUND(AVG(evaluacion),2) AS promedio_eval
  FROM capacitacion_asistencias
  $where
";
$kpi = $pdo->prepare($sqlKpi);
$kpi->execute($params);
$kpiRow = $kpi->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'asistieron'=>0,'no_asistieron'=>0,'promedio_eval'=>0];

/* Resumen por curso */
$sqlCursos = "
  SELECT
    curso,
    COUNT(*)                                AS total,
    SUM(CASE WHEN asistencia='ASISTIÓ' THEN 1 ELSE 0 END) AS asistieron,
    SUM(CASE WHEN asistencia='NO ASISTIÓ' THEN 1 ELSE 0 END) AS no_asistieron,
    ROUND(AVG(evaluacion),2)               AS promedio_eval
  FROM capacitacion_asistencias
  $where
  GROUP BY curso
  ORDER BY curso
";
$stmt = $pdo->prepare($sqlCursos);
$stmt->execute($params);
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'kpi'    => $kpiRow,
  'cursos' => $cursos
], JSON_UNESCAPED_UNICODE);
