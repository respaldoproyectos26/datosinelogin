<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php'; // ajusta profundidad
require_once GEOLOC_URL . '/api/_geo_helpers.php';
set_cache_headers('json-nocache');

if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

// Si es POST/PUT/DELETE → CSRF obligatorio:
csrf_check();

if ($_POST['clave'] !== 'jxd1h3as7') {
    exit("❌ Clave incorrecta.");
}

$apiKey = 'AIzaSyA353t1pi7farhTze7KjrKvKAnYKsZKkZ4';
$total = $insertados = $repetidos = $fallos = 0;
$resultados = [];

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    exit("Error al subir el archivo.");
}

$nombreArchivo = $_FILES['archivo']['tmp_name'];
$handle = fopen($nombreArchivo, 'r');
if (!$handle) exit("No se pudo leer el archivo CSV.");

while (($fila = fgetcsv($handle, 1000, ",")) !== false) {
    $total++;
    $fila = array_map('trim', $fila);

    if (count($fila) < 21) {
        $fallos++;
        continue;
    }

    list($municipio, $referencia, $nivel, $fechanac, $edad, $ine, $curp,
         $nombre, $paterno, $materno, $telefono, $calle, $n_ext, $n_int,
         $colonia, $cp, $seccion, $estatus, $observaciones, $enlaceassoc,
         $categoria) = array_pad($fila, 21, null);

    // Validar datos clave
    if (!$municipio || !$calle || !$colonia || !$cp || !$nombre) {
        $fallos++;
        continue;
    }

    // Convertir fecha
    $fechanac_fmt = null;
    if ($fechanac) {
        $ts = strtotime(str_replace('/', '-', $fechanac));
        if ($ts !== false) $fechanac_fmt = date('Y-m-d', $ts);
    }

    // Construir dirección
    $direccion = sprintf("%s %s %s, Col. %s, CP %s, %s, Estado de México",
        $calle, $n_ext, $n_int, $colonia, $cp, $municipio);

    // Buscar dirección en la base de datos...
    $stmt = $pdo->prepare("SELECT id FROM ubicaciones WHERE direccion = ?");
    $stmt->execute([$direccion]);
    $id_ubicacion = $stmt->fetchColumn();

    if (!$id_ubicacion) {
        // Geocodificar
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($direccion) . "&key=$apiKey";
        $response = file_get_contents($url);
        $geo = json_decode($response, true);

        if ($geo['status'] !== 'OK') {
            $fallos++;
            continue;
        }

        $loc = $geo['results'][0]['geometry']['location'];
        $lat = $loc['lat'];
        $lng = $loc['lng'];

        $stmt = $pdo->prepare("INSERT INTO ubicaciones (direccion, lat, lng) VALUES (?, ?, ?)");
        $stmt->execute([$direccion, $lat, $lng]);
        $id_ubicacion = $pdo->lastInsertId();
    } else {
        $repetidos++;
    }

    // Insertar usuario
    $stmt = $pdo->prepare("INSERT IGNORE INTO registros_usuarios (
        municipio, referencia, nivel, fechanac, edad, ine, curp,
        nombre, paterno, materno, telefono, calle, numext, numint,
        colonia, cp, seccion, estatus, observaciones, enlaceassoc,
        categoria, id_ubicacion
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->execute([
        $municipio, $referencia, $nivel, $fechanac_fmt, $edad, $ine, $curp,
        $nombre, $paterno, $materno, $telefono, $calle, $n_ext, $n_int,
        $colonia, $cp, $seccion, $estatus, $observaciones, $enlaceassoc,
        $categoria, $id_ubicacion
    ]);

    $insertados++;
}
fclose($handle);

// Mostrar resultados
echo "<h4>Total: $total | Insertados: $insertados | Repetidos: $repetidos | Fallos: $fallos</h4>";


/* Logging útil (por archivo)

En un masivo, añade log por archivo:
$logFile = __DIR__.'/../logs/geocode_'.date('Ymd').'.log';
file_put_contents($logFile, "[".date('H:i:s')."] $status\t$direccion\n", FILE_APPEND);

*/