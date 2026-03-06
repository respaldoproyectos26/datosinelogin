<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

// Es POST → CSRF (aunque sea “lectura”, así lo estás llamando)
csrf_check();

$pdo = db();

$enlace = trim($_GET['enlaceassoc'] ?? '');
if (!$enlace) {
    echo json_encode([]); exit;
}

$sql = "SELECT nombre, paterno, materno, telefono, calle, numext, numint 
        FROM registros_usuarios WHERE enlaceassoc = ?
        WHERE 1=1";
$params = [];

$stmt = $pdo->prepare($sql);
$stmt->execute([$enlace]);
json($stmt->fetchAll(PDO::FETCH_ASSOC));
