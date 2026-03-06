<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

// Es POST → CSRF (aunque sea “lectura”, así lo estás llamando)
csrf_check();

$pdo = db();
$idedo = trim((string)($_POST['idedo'] ?? '')); // puede ser '24' o 'SAN LUIS POTOSI'

if ($idedo === '') json([], 200);

try {
  if (ctype_digit($idedo)) {
    // por ID de entidad (mejor)
    $stmt = $pdo->prepare(
      "SELECT idmunicalc AS mun_code, municalc AS mun_name
       FROM municalc
       WHERE idedo = :idedo
       ORDER BY idmunicalc"
    );
    $stmt->bindValue(':idedo', (int)$idedo, PDO::PARAM_INT);
  } else {
    // por nombre de entidad (fallback)
    $stmt = $pdo->prepare(
      "SELECT idmunicalc AS mun_code, municalc AS mun_name
       FROM municalc
       WHERE UPPER(TRIM(nomestado)) = UPPER(TRIM(:nomestado))
       ORDER BY idmunicalc"
    );
    $stmt->bindValue(':nomestado', $idedo, PDO::PARAM_STR);
  }

  $stmt->execute();

  json($stmt->fetchAll(PDO::FETCH_ASSOC), 200);
  /*
  Sin json() del helper response.php es
  echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
  */
} catch (Throwable $e) {
  error_log('get_municipios error: '.$e->getMessage());
  json(['error'=>'DB','msg'=>'Error al consultar'], 500);
  /*
  Sin json() del helper response.php es
  echo json_encode(['error'=>'DB', 'msg'=>'Error al consultar'], JSON_UNESCAPED_UNICODE);
  */
}
