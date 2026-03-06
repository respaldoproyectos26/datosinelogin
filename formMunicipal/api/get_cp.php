<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
require_login();

function json_out($arr){ echo json_encode($arr, JSON_UNESCAPED_UNICODE); exit; }

$cp = preg_replace('/\D+/', '', $_POST['cp'] ?? '');
$cp = str_pad(substr($cp,0,5), 5, '0', STR_PAD_LEFT);
if (strlen($cp) !== 5) json_out(['ok'=>false,'msg'=>'CP inválido']);

$pdo = db();

$st = $pdo->prepare("
  SELECT estado, municipio,
         GROUP_CONCAT(DISTINCT colonia ORDER BY colonia SEPARATOR '||') AS colonias
  FROM cp_catalogo
  WHERE cp = ?
  GROUP BY estado, municipio
  LIMIT 1
");
$st->execute([$cp]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
  json_out(['ok'=>true,'data'=>['found'=>false]]);
}

// === Alias de estado para empatar con tu tabla `estado` ===
$estado = $row['estado'];
$alias = [
  'COAHUILA DE ZARAGOZA' => 'COAHUILA',
  'MICHOACAN DE OCAMPO'  => 'MICHOACAN',
  'VERACRUZ DE IGNACIO DE LA LLAVE' => 'VERACRUZ',
];
if (isset($alias[$estado])) $estado = $alias[$estado];

$colonias = $row['colonias'] ? explode('||', $row['colonias']) : [];

json_out([
  'ok'=>true,
  'data'=>[
    'found'=>true,
    'estado'=>$estado,
    'municipio'=>$row['municipio'],
    'colonias'=>$colonias
  ]
]);
