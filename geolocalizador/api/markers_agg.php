<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

require_once HELPERS_DIR . '/security.php';
if (!rateLimit('map:agg', 240, 60)) json(['error'=>'RATE_LIMIT'], 429);

$pdo = db();

// ----------------- Parámetros -----------------
$categoria = trim((string)($_GET['categoria'] ?? ''));
$estatus   = trim((string)($_GET['estatus'] ?? ''));

$nelat = isset($_GET['nelat']) ? filter_var($_GET['nelat'], FILTER_VALIDATE_FLOAT) : null;
$nelng = isset($_GET['nelng']) ? filter_var($_GET['nelng'], FILTER_VALIDATE_FLOAT) : null;
$swlat = isset($_GET['swlat']) ? filter_var($_GET['swlat'], FILTER_VALIDATE_FLOAT) : null;
$swlng = isset($_GET['swlng']) ? filter_var($_GET['swlng'], FILTER_VALIDATE_FLOAT) : null;

$zoom = (int)($_GET['zoom'] ?? 8);
$zoom = max(3, min($zoom, 20));

function grid_for_zoom(int $z): float {
  if ($z <= 7)  return 0.50;
  if ($z <= 8)  return 0.25;
  if ($z <= 9)  return 0.12;
  if ($z <= 10) return 0.06;
  if ($z <= 11) return 0.03;
  if ($z <= 12) return 0.015;
  if ($z <= 13) return 0.008;
  return 0.004;
}

// bounds
$haveBounds = ($nelat !== null && $nelng !== null && $swlat !== null && $swlng !== null);
if (!$haveBounds) json(['error'=>'NO_BOUNDS'], 400);

if (
  $nelat < -90 || $nelat > 90 || $swlat < -90 || $swlat > 90 ||
  $nelng < -180 || $nelng > 180 || $swlng < -180 || $swlng > 180
) json(['error'=>'BAD_BOUNDS'], 400);

$minLat = min($swlat, $nelat);
$maxLat = max($swlat, $nelat);
$minLng = min($swlng, $nelng);
$maxLng = max($swlng, $nelng);

// ajusta si tu BD guarda con acento
$estado = 'SAN LUIS POTOSI';

$grid = grid_for_zoom($zoom);
$gridSql = sprintf('%.6f', $grid);
$halfSql = sprintf('%.6f', $grid / 2.0);

$limit = 5000;

// tablas permitidas (whitelist)
$SOURCES = [
  'registrorepestatal',
  'registrorepmunicipal',
  'registrorepdistfed',
  'registrorepdistloc',
  'registrofig',
  'registrofig2',
  'registrofig3',
];

$union  = [];
$params = [];

$clatExpr = "FLOOR(u.lat / {$gridSql})";
$clngExpr = "FLOOR(u.lng / {$gridSql})";

$i = 0;
foreach ($SOURCES as $t) {
  $i++;

  // placeholders únicos por SELECT (evita HY093)
  $pMinLat  = ":minLat{$i}";
  $pMaxLat  = ":maxLat{$i}";
  $pMinLng  = ":minLng{$i}";
  $pMaxLng  = ":maxLng{$i}";
  $pEstado  = ":estado{$i}";
  $pFigura  = ":figura{$i}";
  $pEst     = ":est{$i}";

  $params[$pMinLat] = $minLat;
  $params[$pMaxLat] = $maxLat;
  $params[$pMinLng] = $minLng;
  $params[$pMaxLng] = $maxLng;
  $params[$pEstado] = $estado;

  $where = "
    u.lat IS NOT NULL AND u.lng IS NOT NULL
    AND u.lat BETWEEN {$pMinLat} AND {$pMaxLat}
    AND u.lng BETWEEN {$pMinLng} AND {$pMaxLng}
    AND r.estado = {$pEstado}
  ";

  if ($categoria !== '') {
    $where .= " AND ru.figura = {$pFigura}";
    $params[$pFigura] = $categoria;
  }

  if ($estatus !== '') {
    if (mb_strtoupper($estatus,'UTF-8') === 'NO ASIGNADO') {
      $where .= " AND (ru.estatus IS NULL OR TRIM(ru.estatus)='' OR UPPER(TRIM(ru.estatus))='NO ASIGNADO')";
    } else {
      $where .= " AND ru.estatus = {$pEst}";
      $params[$pEst] = $estatus;
    }
  }

  $union[] = "
    SELECT
      {$clatExpr} AS clat,
      {$clngExpr} AS clng,
      COUNT(*) AS cnt
    FROM {$t} r
    JOIN registros_usuarios_login ru ON ru.id = r.id_reglogin
    JOIN ubicaciones_login u        ON u.id  = ru.id_ubicacion
    WHERE {$where}
    GROUP BY {$clatExpr}, {$clngExpr}
  ";
}

$sql = "
  SELECT
    (clat * {$gridSql} + {$halfSql}) AS lat,
    (clng * {$gridSql} + {$halfSql}) AS lng,
    SUM(cnt) AS count
  FROM (
    ".implode("\nUNION ALL\n", $union)."
  ) z
  GROUP BY clat, clng
  ORDER BY count DESC
  LIMIT :lim
";

try {
  $stmt = $pdo->prepare($sql);

  foreach ($params as $k => $v) {
    // floats y strings como STR para evitar líos
    $stmt->bindValue($k, (string)$v, PDO::PARAM_STR);
  }
  $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);

  $stmt->execute();
  json($stmt->fetchAll(PDO::FETCH_ASSOC), 200);

} catch (Throwable $e) {
  error_log('markers_agg SQL error: '.$e->getMessage());

  if (defined('APP_DEBUG') && APP_DEBUG) {
    json([
      'error'  => 'SERVER_ERROR',
      'detail' => $e->getMessage(),
      'file'   => basename($e->getFile()),
      'line'   => $e->getLine(),
    ], 500);
  }
  json(['error'=>'SERVER_ERROR'], 500);
}
