<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login();
if (!can('ver_resumen_asistencia')) exit(json_encode(['error' => 'No autorizado']));

$pdo = db();

// DataTables params
$start  = intval($_GET['start'] ?? 0);
$length = intval($_GET['length'] ?? 25);
$search = trim($_GET['search']['value'] ?? '');

// Construcción base
$where = '';
$params = [];

if ($search !== '') {
    $where = "WHERE usuario LIKE ? OR titulo LIKE ?";
    $params = ["%$search%", "%$search%"];
}

$sql = "
SELECT usuario, 'Manual' AS tipo, DATE(a.hora_entrada) AS fecha,
       a.hora_entrada AS inicio, a.hora_salida AS fin,
       IFNULL(a.duracion_min, 0) AS duracion_min
FROM asistencia_capacitacion a
JOIN ine_usuarios u ON u.id = a.id_usuario
UNION ALL
SELECT u.usuario, 'Media' AS tipo, DATE(s.inicio) AS fecha,
       s.inicio, s.fin, ROUND(s.duracion_sec / 60, 1) AS duracion_min
FROM media_sesiones s
JOIN ine_usuarios u ON u.id = s.user_id
" . ($where ? "WHERE u.usuario LIKE ? OR s.inicio LIKE ?" : "") . "
ORDER BY fecha DESC
LIMIT $start, $length
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total general
$total = $pdo->query("
  SELECT (SELECT COUNT(*) FROM asistencia_capacitacion) +
         (SELECT COUNT(*) FROM media_sesiones)
")->fetchColumn();

echo json_encode([
  "draw" => intval($_GET['draw'] ?? 0),
  "recordsTotal" => $total,
  "recordsFiltered" => $total,
  "data" => $data
]);
