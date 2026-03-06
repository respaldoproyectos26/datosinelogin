<?php
require('../../../vendor/autoload.php');
require('../../../includes/db.php');
require('../../../includes/fpdf/fpdf.php');

$pdo = db();
$filtroCurso = $_GET['curso'] ?? '';
$fechaInicio = $_GET['inicio'] ?? '';
$fechaFin = $_GET['fin'] ?? '';

$query = "SELECT nombre, curso, fecha, hora_entrada, hora_salida, asistencia, evaluacion FROM capacitacion_asistencias WHERE 1=1";
$params = [];
if ($filtroCurso) { $query .= " AND curso LIKE :curso"; $params[':curso']="%$filtroCurso%"; }
if ($fechaInicio && $fechaFin) { $query .= " AND fecha BETWEEN :inicio AND :fin"; $params[':inicio']=$fechaInicio; $params[':fin']=$fechaFin; }

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Reporte de Asistencia / Capacitacion',0,1,'C');

// Totales rápidos
$sql = "
  SELECT
    COUNT(*) total,
    SUM(CASE WHEN asistencia='ASISTIÓ' THEN 1 ELSE 0 END) si,
    SUM(CASE WHEN asistencia='NO ASISTIÓ' THEN 1 ELSE 0 END) no_,
    ROUND(AVG(evaluacion),2) prom
  FROM capacitacion_asistencias
  WHERE 1=1
  " . ($filtroCurso ? " AND curso LIKE :curso" : "") .
  (($fechaInicio && $fechaFin) ? " AND fecha BETWEEN :inicio AND :fin" : "");

$st = $pdo->prepare($sql);
if ($filtroCurso) $st->bindValue(':curso', "%$filtroCurso%");
if ($fechaInicio && $fechaFin) { $st->bindValue(':inicio',$fechaInicio); $st->bindValue(':fin',$fechaFin); }
$st->execute();
$tot = $st->fetch(PDO::FETCH_ASSOC) ?: ['total'=>0,'si'=>0,'no_'=>0,'prom'=>0];

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,8, mb_convert_encoding("Total: {$tot['total']}   Asistieron: {$tot['si']}   No asistieron: {$tot['no_']}   Prom. eval: {$tot['prom']}", 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(2);

$pdf->Ln(5);
$pdf->SetFont('Arial','B',10);
foreach(array_keys($data[0] ?? []) as $col){ $pdf->Cell(40,7,$col,1,0,'C'); }
$pdf->Ln();

$pdf->SetFont('Arial','',9);
foreach($data as $row){
  foreach($row as $val){ $pdf->Cell(40,7,mb_convert_encoding($val, 'ISO-8859-1', 'UTF-8'),1); }
  $pdf->Ln();
}
$pdf->Output('I','reporte_asistencia.pdf');
