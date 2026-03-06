<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php'; // ajusta profundidad
require_once GEOLOC_URL . '/api/_geo_helpers.php';
set_cache_headers('json-nocache');

if (!tiene_permiso('registrar_manual_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

// Si es POST/PUT/DELETE → CSRF obligatorio:
csrf_check();

$apiKey = 'AIzaSyA353t1pi7farhTze7KjrKvKAnYKsZKkZ4';

function limpiar($v) {
    return trim($v ?? '');
}

$nombre = limpiar($_POST['nombre']);
$paterno = limpiar($_POST['paterno']);
$materno = limpiar($_POST['materno']);
$calle = limpiar($_POST['calle']);
$numext = limpiar($_POST['numext']);
$numint = limpiar($_POST['numint']);
$colonia = limpiar($_POST['colonia']);
$cp = limpiar($_POST['cp']);
$municipio = limpiar($_POST['municipio']);
$categoria = limpiar($_POST['categoria']);

$direccion = "$calle $numext $numint, Col. $colonia, CP $cp, $municipio, Estado de México";

$stmt = $pdo->prepare("SELECT id FROM ubicaciones WHERE direccion = ?");
$stmt->execute([$direccion]);
$id_ubicacion = $stmt->fetchColumn();

if (!$id_ubicacion) {
    $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($direccion) . "&key=$apiKey";
    $resp = file_get_contents($url);
    $geo = json_decode($resp, true);

    if (!isset($geo['status']) || $geo['status'] !== 'OK') {
        exit("No se pudo geolocalizar la dirección.");
    }

    $loc = $geo['results'][0]['geometry']['location'];
    $stmt = $pdo->prepare("INSERT INTO ubicaciones (direccion, lat, lng) VALUES (?, ?, ?)");
    $stmt->execute([$direccion, $loc['lat'], $loc['lng']]);
    $id_ubicacion = $pdo->lastInsertId();
}

// Insertar registro
$stmt = $pdo->prepare("INSERT INTO registros_usuarios_login (
    nombre, paterno, materno, calle, numext, numint, colonia, cp,
    municipio, categoria, id_ubicacion
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $nombre, $paterno, $materno, $calle, $numext, $numint,
    $colonia, $cp, $municipio, $categoria, $id_ubicacion
]);

header('Location: mapa.php');
exit;
