<?php
// formMunicipal/geo_helpers.php
require_once __DIR__ . '/../config/keys.php';

function normaliza_direccion(
  string $calle, string $numext, ?string $numint,
  string $colonia, string $cp, string $municipio, string $estado
): string {
  $dir = trim(preg_replace('/\s+/', ' ',
    sprintf('%s %s%s, Col. %s, CP %s, %s, %s',
      $calle,
      $numext,
      ($numint ? " Int. $numint" : ''),
      $colonia, $cp, $municipio, $estado
    )
  ));
  return mb_strtoupper($dir, 'UTF-8');
}

function http_get_json(string $url, int $timeout=8): array {
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => $timeout,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => true,
  ]);
  $out = curl_exec($ch);
  if ($out === false) throw new RuntimeException('HTTP error: '.curl_error($ch));
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code < 200 || $code >= 300) throw new RuntimeException('HTTP status '.$code);
  $json = json_decode($out, true);
  if (!is_array($json)) throw new RuntimeException('JSON inválido');
  return $json;
}

function geocode_with_backoff(string $url, int $baseDelayMs = 250, int $maxBackoffSec = 16): array {
  $retries = 0;
  while (true) {
    $json = http_get_json($url, 8);
    $status = $json['status'] ?? 'UNKNOWN_ERROR';

    if ($status === 'OK') {
      usleep($baseDelayMs * 1000); // ~ 4 req/s
      return $json;
    }
    if ($status === 'OVER_QUERY_LIMIT' || $status === 'UNKNOWN_ERROR') {
      $sleep = min($maxBackoffSec, max(1, 2 ** $retries));
      error_log("[GEOCODE] $status → retry in {$sleep}s (retry #{$retries})");
      sleep($sleep);
      $retries++;
      continue;
    }
    // ZERO_RESULTS, REQUEST_DENIED, INVALID_REQUEST, etc.
    return $json;
  }
}

/**
 * Crea (o reutiliza) una fila en ubicaciones_login y devuelve su ID.
 * Usa la clave backend restringida por IP (GEOCODING_API_KEY).
 */
function ensure_ubicacion(PDO $pdo, string $direccion): int {
  $direccion = trim($direccion);
  if ($direccion === '') throw new InvalidArgumentException('Dirección vacía');

  // 1) buscar
  $sel = $pdo->prepare("SELECT id FROM ubicaciones_login WHERE direccion = ? LIMIT 1");
  $sel->execute([$direccion]);
  $id = (int)$sel->fetchColumn();
  if ($id) return $id;

  // 2) geocode
  $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" .
          urlencode($direccion) . "&key=" . GEOCODING_API_KEY;
  $geo = geocode_with_backoff($url);

  $status = $geo['status'] ?? 'UNKNOWN_ERROR';
  if ($status !== 'OK') {
    error_log("[GEOCODE] {$status} dir='{$direccion}'");
    throw new RuntimeException('Geocoding falló: '.$status);
  }

  $loc = $geo['results'][0]['geometry']['location'] ?? null;
  if (!$loc) throw new RuntimeException('Geocoding sin geometry');

  // 3) insertar con control de duplicado
  try {
    $ins = $pdo->prepare("
      INSERT INTO ubicaciones_login (direccion, lat, lng, source, estatus, fecha)
      VALUES (?, ?, ?, 'google', 'ACTIVO', NOW())
    ");
    $ins->execute([$direccion, $loc['lat'], $loc['lng']]);
    return (int)$pdo->lastInsertId();

  } catch (PDOException $e) {
    // 1062 = Duplicate entry (MySQL)
    if ((int)($e->errorInfo[1] ?? 0) === 1062) {
      $sel->execute([$direccion]);
      $id = (int)$sel->fetchColumn();
      if ($id) return $id;
    }
    throw $e;
  }
}