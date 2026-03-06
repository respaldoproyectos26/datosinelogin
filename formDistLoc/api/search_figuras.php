<?php
// api/search_figuras.php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
require_login();

$SCOPE_TABLE = [
  'ESTATAL'           => 'registrorepestatal',
  'MUNICIPAL'         => 'registrorepmunicipal',
  'DISTRITAL FEDERAL' => 'registrorepdistfed',
  'DISTRITAL LOCAL'   => 'registrorepdistloc',
];

$scope  = mb_strtoupper(trim((string)($_GET['scope'] ?? '')));
$q      = trim((string)($_GET['q'] ?? ''));
$estado = trim((string)($_GET['estado'] ?? ''));

if (!isset($SCOPE_TABLE[$scope]) || ($q === '' && $estado === '')) {
  echo json_encode([]); exit;
}

try {
  $pdo  = db();
  $sql  = "SELECT id, estado, paterno, materno, nombre, seccion
           FROM {$SCOPE_TABLE[$scope]} WHERE 1=1";
  $par  = [];

  if ($estado !== '') { $sql .= " AND UPPER(TRIM(estado)) = UPPER(TRIM(:edo))"; $par[':edo'] = $estado; }
  if ($q !== '')      { $sql .= " AND (nombre LIKE :q OR paterno LIKE :q OR materno LIKE :q)"; $par[':q']="%$q%"; }
  $sql .= " ORDER BY estado, paterno, materno, nombre LIMIT 20";

  $st = $pdo->prepare($sql);
  $st->execute($par);
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(array_map(fn($r)=>[
    'id'    => $r['id'],
    'label' => "{$r['nombre']} {$r['paterno']} {$r['materno']} — {$r['estado']} (Sec. {$r['seccion']})"
  ], $rows));
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error'=>'Error en la base de datos']);
}