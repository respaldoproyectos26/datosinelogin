<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login();

$pdo = db();
$id_usuario = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT id, hora_entrada FROM asistencia_capacitacion 
                       WHERE id_usuario = ? AND fecha = CURRENT_DATE AND hora_salida IS NULL");
$stmt->execute([$id_usuario]);
$row = $stmt->fetch();

if (!$row) {
  echo json_encode(['status'=>'error','mensaje'=>'No hay entrada registrada hoy.']);
  exit;
}

$entrada = new DateTime($row['hora_entrada']);
$salida = new DateTime();
$duracion = $entrada->diff($salida)->h * 60 + $entrada->diff($salida)->i;

$stmt = $pdo->prepare("UPDATE asistencia_capacitacion 
                       SET hora_salida = NOW(), duracion_min = ? WHERE id = ?");
$stmt->execute([$duracion, $row['id']]);

echo json_encode(['status'=>'ok','mensaje'=>'Salida registrada. Duración: '.$duracion.' min.']);
