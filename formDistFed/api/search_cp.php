<?php
declare(strict_types=1);

require_once __DIR__ . '/../../helpers/bootstrap_api.php';
require_login();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
  header('Allow: GET');
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido']);
  exit;
}

// Si esto requiere login, valida aquí.
// Si es público, omite esta línea.
// require_login();

$pdo = db();

$q = trim((string)($_GET['q'] ?? ''));

// Si no hay query, no regreses todo
if ($q === '') {
  echo json_encode([], JSON_UNESCAPED_UNICODE);
  exit;
}

// Normaliza: solo dígitos y máx 5
$cp = preg_replace('/\D+/', '', $q);
$cp = substr($cp, 0, 5);

// Permite desde 2 dígitos para autocomplete
if (strlen($cp) < 2) {
  echo json_encode([], JSON_UNESCAPED_UNICODE);
  exit;
}

$table = 'cp_catalogo'; // ajusta si aplica

$sql = "SELECT cp, estado, municipio
        FROM {$table}
        WHERE cp LIKE :cp
        GROUP BY cp, estado, municipio
        ORDER BY cp
        LIMIT 20";

$st = $pdo->prepare($sql);
$st->execute([':cp' => $cp . '%']);

$rows = $st->fetchAll(PDO::FETCH_ASSOC);

$out = array_map(static function(array $r): array {
  $cp  = (string)($r['cp'] ?? '');
  $edo = (string)($r['estado'] ?? '');
  $mun = (string)($r['municipio'] ?? '');
  $label = trim($cp . ' — ' . $mun . ', ' . $edo, ' ,—');

  return [
    'value'     => $cp,
    'text'      => $label,
    'cp'        => $cp,
    'estado'    => $edo,
    'municipio' => $mun,
  ];
}, $rows);

echo json_encode($out, JSON_UNESCAPED_UNICODE);