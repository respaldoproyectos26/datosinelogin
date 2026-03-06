<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login();

$pdo = db();
$id_usuario = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT fecha, hora_entrada, hora_salida, duracion_min 
                       FROM asistencia_capacitacion 
                       WHERE id_usuario = ? ORDER BY fecha DESC LIMIT 10");
$stmt->execute([$id_usuario]);

echo '<table class="table table-striped table-sm">';
echo '<thead><tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Duración (min)</th></tr></thead><tbody>';
foreach ($stmt as $row) {
  echo '<tr>';
  echo '<td>' . htmlspecialchars($row['fecha']) . '</td>';
  echo '<td>' . htmlspecialchars($row['hora_entrada']) . '</td>';
  echo '<td>' . htmlspecialchars($row['hora_salida'] ?? '-') . '</td>';
  echo '<td>' . htmlspecialchars($row['duracion_min'] ?? '-') . '</td>';
  echo '</tr>';
}
echo '</tbody></table>';
