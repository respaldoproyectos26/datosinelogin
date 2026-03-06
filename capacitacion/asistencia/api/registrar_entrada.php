<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login();

$pdo = db();
$id_usuario = $_SESSION['user']['id'];
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$ua = $_SERVER['HTTP_USER_AGENT'] ?? null;

// Verifica si ya hay entrada sin salida
$stmt = $pdo->prepare("SELECT * FROM asistencia_capacitacion 
                       WHERE id_usuario = ? AND fecha = CURRENT_DATE AND hora_salida IS NULL");
$stmt->execute([$id_usuario]);

if ($stmt->fetch()) {
    echo json_encode(['status'=>'error','mensaje'=>'Ya registraste tu entrada hoy.']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO asistencia_capacitacion (id_usuario, hora_entrada, ip, user_agent) 
                       VALUES (?, NOW(), ?, ?)");
$stmt->execute([$id_usuario, $ip, $ua]);

echo json_encode(['status'=>'ok','mensaje'=>'Entrada registrada exitosamente.']);