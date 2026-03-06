<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

// Es POST → CSRF (aunque sea “lectura”, así lo estás llamando)
csrf_check();

$pdo = db();

// idmunicalc viene del <select> con value = código
$idmunicalc = $_POST['idmunicalc'] ?? '';
$idedo      = $_POST['idedo']      ?? '';   // opcional, por si quieres filtrar por estado también

if ($idmunicalc === '' || !ctype_digit((string)$idmunicalc)) {
  echo json_encode(["error" => "No se recibió un municipio válido"]);
  exit;
}

try {
  // Con o sin filtro de estado:
  if ($idedo !== '' && ctype_digit((string)$idedo)) {
    $sql = "SELECT DISTINCT seccion
            FROM secciones
            WHERE idmunicalc = :idmunicalc AND idedo = :idedo
            ORDER BY (seccion + 0)";              // orden numérico
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idmunicalc', (int)$idmunicalc, PDO::PARAM_INT);
    $stmt->bindValue(':idedo',      (int)$idedo,      PDO::PARAM_INT);
  } else {
    $sql = "SELECT DISTINCT seccion
            FROM secciones
            WHERE idmunicalc = :idmunicalc
            ORDER BY (seccion + 0)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idmunicalc', (int)$idmunicalc, PDO::PARAM_INT);
  }

  $stmt->execute();
  $secciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($secciones, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  error_log('get_secciones error: '.$e->getMessage());
  http_response_code(500);
  echo json_encode(["error" => "Error en la base de datos"]);
}
