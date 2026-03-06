<?php
require '../../../vendor/autoload.php';
include_once '../../../includes/db.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo = db();
$filtroCurso = $_GET['curso'] ?? '';
$fechaInicio = $_GET['inicio'] ?? '';
$fechaFin = $_GET['fin'] ?? '';

$query = "SELECT nombre, curso, fecha, hora_entrada, hora_salida, asistencia, evaluacion, observaciones FROM capacitacion_asistencias WHERE 1=1";
$params = [];
if ($filtroCurso) { $query .= " AND curso LIKE :curso"; $params[':curso'] = "%$filtroCurso%"; }
if ($fechaInicio && $fechaFin) { $query .= " AND fecha BETWEEN :inicio AND :fin"; $params[':inicio']=$fechaInicio; $params[':fin']=$fechaFin; }

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(array_keys($data[0] ?? []), null, 'A1');
$sheet->fromArray($data, null, 'A2');
$sheet->setTitle('Asistencia');

// ===== Hoja 2: Resumen por curso =====
$sheet2 = $spreadsheet->createSheet();
$sheet2->setTitle('Resumen por curso');

// Reusar lógica de filtros
$where = " WHERE 1=1 ";
$params2 = [];
if ($filtroCurso) { $where .= " AND curso LIKE :curso"; $params2[':curso']="%$filtroCurso%"; }
if ($fechaInicio && $fechaFin) { $where .= " AND fecha BETWEEN :inicio AND :fin"; $params2[':inicio']=$fechaInicio; $params2[':fin']=$fechaFin; }

$sqlResumen = "
  SELECT
    curso,
    COUNT(*) AS total,
    SUM(CASE WHEN asistencia='ASISTIÓ' THEN 1 ELSE 0 END) AS asistieron,
    SUM(CASE WHEN asistencia='NO ASISTIÓ' THEN 1 ELSE 0 END) AS no_asistieron,
    ROUND(AVG(evaluacion),2) AS promedio_eval
  FROM capacitacion_asistencias
  $where
  GROUP BY curso
  ORDER BY curso
";
$st2 = $pdo->prepare($sqlResumen);
$st2->execute($params2);
$resumen = $st2->fetchAll(PDO::FETCH_ASSOC);

// Headers
$sheet2->fromArray(['Curso','Total','Asistieron','No asistieron','Prom. evaluación'], null, 'A1');
if ($resumen) {
  foreach ($resumen as $i => $r) {
    $row = $i + 2;
    $sheet2->setCellValue("A{$row}", $r['curso']);
    $sheet2->setCellValue("B{$row}", (int)$r['total']);
    $sheet2->setCellValue("C{$row}", (int)$r['asistieron']);
    $sheet2->setCellValue("D{$row}", (int)$r['no_asistieron']);
    $sheet2->setCellValue("E{$row}", (float)$r['promedio_eval']);
  }
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_asistencia.xlsx"');
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
