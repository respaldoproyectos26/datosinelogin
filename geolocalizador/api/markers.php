<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

require_once HELPERS_DIR . '/security.php';
if (!rateLimit('map:points', 240, 60)) json(['error'=>'RATE_LIMIT'], 429);

$pdo = db();

// ----------------- Parámetros -----------------
$categoria = trim((string)($_GET['categoria'] ?? '')); // figura
$estatus   = trim((string)($_GET['estatus'] ?? ''));

$nelat  = filter_var($_GET['nelat'] ?? null, FILTER_VALIDATE_FLOAT);
$nelng  = filter_var($_GET['nelng'] ?? null, FILTER_VALIDATE_FLOAT);
$swlat  = filter_var($_GET['swlat'] ?? null, FILTER_VALIDATE_FLOAT);
$swlng  = filter_var($_GET['swlng'] ?? null, FILTER_VALIDATE_FLOAT);

$limit  = max(1, min((int)($_GET['limit'] ?? 5000), 20000));
$offset = max(0, (int)($_GET['offset'] ?? 0));

$haveBounds = ($nelat !== null && $nelng !== null && $swlat !== null && $swlng !== null);
if (!$haveBounds) json(['error'=>'NO_BOUNDS'], 400);

$minLat = min($swlat, $nelat);
$maxLat = max($swlat, $nelat);
$minLng = min($swlng, $nelng);
$maxLng = max($swlng, $nelng);

// estado por entidad
$ESTADOS = ['24' => 'SAN LUIS POTOSI'];
$entidadRaw = trim((string)($_GET['entidad'] ?? ''));
$estado = (ctype_digit($entidadRaw) && isset($ESTADOS[$entidadRaw])) ? $ESTADOS[$entidadRaw] : 'SAN LUIS POTOSI';

$SOURCES = [
  ['table'=>'registrorepestatal',   'label'=>'REP ESTATAL'],
  ['table'=>'registrorepmunicipal', 'label'=>'REP MUNICIPAL'],
  ['table'=>'registrorepdistfed',   'label'=>'REP DIST FED'],
  ['table'=>'registrorepdistloc',   'label'=>'REP DIST LOC'],
  ['table'=>'registrofig',          'label'=>'RG'],
  ['table'=>'registrofig2',         'label'=>'RC'],
  ['table'=>'registrofig3',         'label'=>'PROMOVIDO'],
];

$union = [];
$params = [];
$i = 0;

foreach ($SOURCES as $src) {
  $i++;
  $t = $src['table'];
  $label = $src['label'];

  // placeholders únicos
  $pEstado = ":estado{$i}";
  $pMinLat = ":minLat{$i}";
  $pMaxLat = ":maxLat{$i}";
  $pMinLng = ":minLng{$i}";
  $pMaxLng = ":maxLng{$i}";
  $pFigura = ":figura{$i}";
  $pEst    = ":est{$i}";

  $params[$pEstado] = $estado;
  $params[$pMinLat] = $minLat;
  $params[$pMaxLat] = $maxLat;
  $params[$pMinLng] = $minLng;
  $params[$pMaxLng] = $maxLng;

  $where = "
    u.lat IS NOT NULL AND u.lng IS NOT NULL
    AND u.lat BETWEEN {$pMinLat} AND {$pMaxLat}
    AND u.lng BETWEEN {$pMinLng} AND {$pMaxLng}
    AND UPPER(TRIM(r.estado)) = UPPER(TRIM({$pEstado}))
  ";

  if ($categoria !== '') {
    $where .= " AND UPPER(TRIM(ru.figura)) = UPPER(TRIM({$pFigura}))";
    $params[$pFigura] = $categoria;
  }

  if ($estatus !== '') {
    if (mb_strtoupper($estatus,'UTF-8') === 'NO ASIGNADO') {
      $where .= " AND (ru.estatus IS NULL OR TRIM(ru.estatus)='' OR UPPER(TRIM(ru.estatus))='NO ASIGNADO')";
    } else {
      $where .= " AND UPPER(TRIM(ru.estatus)) = UPPER(TRIM({$pEst}))";
      $params[$pEst] = $estatus;
    }
  }

  $union[] = "
    SELECT
      '{$t}' AS tabla,
      '{$label}' AS fuente,
      r.id AS id,
      CONCAT_WS(' ', r.nombre, r.paterno, r.materno) AS nombre,
      ru.figura   AS figura,
      ru.telefono AS telefono,
      ru.estatus  AS estatus,
      u.direccion AS direccion,
      (u.lat + 0) AS lat,
      (u.lng + 0) AS lng
    FROM ubicaciones_login u
    JOIN registros_usuarios_login ru ON ru.id_ubicacion = u.id
    JOIN {$t} r                     ON r.id_reglogin   = ru.id
    WHERE {$where}
  ";
}

$sql = "SELECT * FROM (".implode("\nUNION ALL\n", $union).") x
        ORDER BY x.id DESC
        LIMIT :lim OFFSET :off";

try {
  $stmt = $pdo->prepare($sql);

  foreach ($params as $k => $v) {
    $stmt->bindValue($k, (string)$v, PDO::PARAM_STR);
  }
  $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
  $stmt->bindValue(':off', $offset, PDO::PARAM_INT);

  $stmt->execute();
  json($stmt->fetchAll(PDO::FETCH_ASSOC), 200);

} catch (Throwable $e) {
  error_log('markers.php error: '.$e->getMessage());
  json(['error'=>'SERVER_ERROR'], 500);
}