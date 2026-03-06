<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php'; // ajusta profundidad
require_once GEOLOC_URL . '/api/_geo_helpers.php';
set_cache_headers('json-nocache');

if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

// Si es POST/PUT/DELETE → CSRF obligatorio:
csrf_check();

$pdo = db();


if (!isset($_FILES['archivo_csv']) || !is_uploaded_file($_FILES['archivo_csv']['tmp_name'])) {
  echo json_encode(['success'=>false,'message'=>'Archivo no encontrado']);
  exit;
}

$handle = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
$header = fgetcsv($handle);
$errores = []; $ok = 0;

while (($fila = fgetcsv($handle)) !== false) {
  $registro = array_combine($header, $fila);

  $direccion = trim($registro['domicilio'] ?? '');
  if ($direccion === '') { $errores[] = 'Falta domicilio'; continue; }

// 1. Buscar ubicación
  $find = $pdo->prepare("SELECT id FROM ubicaciones_ine WHERE direccion = :d LIMIT 1");
  $find->execute([':d' => $direccion]);
  $idUbi = $find->fetchColumn();

  if (!$idUbi) {
    // 2. Intentar geocodificar (usa helper)
    $geo = geocode_address($direccion);
    if ($geo) {
      $ins = $pdo->prepare("INSERT INTO ubicaciones_ine (direccion, lat, lng, source, estatus, fecha)
                            VALUES (:d, :lat, :lng, 'google', 'ok', NOW())");
      $ins->execute([':d'=>$direccion, ':lat'=>$geo['lat'], ':lng'=>$geo['lng']]);
    } else {
      $ins = $pdo->prepare("INSERT INTO ubicaciones_ine (direccion, lat, lng, source, estatus, fecha)
                            VALUES (:d, NULL, NULL, 'manual', 'pendiente', NOW())");
      $ins->execute([':d'=>$direccion]);
    }
    $idUbi = (int)$pdo->lastInsertId();
  }

  // 3. Insertar en registros_categorias (columnas dinámicas)
  $registro['id_ubicacion'] = $idUbi;
  $campos = array_keys($registro);
  $cols = implode(',', array_map(fn($k)=>"`$k`", $campos));
  $phs  = implode(',', array_map(fn($k)=>":$k", $campos));
  $sql = "INSERT INTO registros_categorias ($cols) VALUES ($phs)";
  try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($registro);
    $ok++;
  } catch (Throwable $e) {
    $errores[] = "Error en '$direccion': ".$e->getMessage();
  }
}
fclose($handle);

echo json_encode([
  'success'=>empty($errores),
  'message'=>empty($errores) ? "OK ($ok registros)" :
            "Parcial ($ok insertados, ".count($errores)." errores)",
  'errores'=>$errores
], JSON_UNESCAPED_UNICODE);