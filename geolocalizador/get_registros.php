<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

$pdo = db();

$categoria = trim($_GET['categoria'] ?? '');
$estatus   = trim($_GET['estatus'] ?? '');

$sql = "SELECT r.*, u.lat, u.lng
        FROM registros_categorias r
        JOIN ubicaciones_ine u ON r.id_ubicacion = u.id
        WHERE 1=1";
$params = [];

if ($categoria !== '') { $sql .= " AND r.Direccion = ?"; $params[] = $categoria; }
if ($estatus !== '')   { $sql .= " AND r.status = ?";    $params[] = $estatus; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
json($stmt->fetchAll(PDO::FETCH_ASSOC));
