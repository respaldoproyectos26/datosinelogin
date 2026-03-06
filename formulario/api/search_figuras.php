<?php
// api/search_figuras.php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
require_login();

$pdo = db();

$scope  = strtoupper(trim($_GET['scope'] ?? '')); // RG | RC
$q      = trim($_GET['q'] ?? '');
$estado = trim($_GET['estado'] ?? '');

$map = ['RG'=>'registrofig', 'RC'=>'registrofig2'];
if (!isset($map[$scope]) || ($q==='' && $estado==='')) { echo json_encode([]); exit; }

$sql = "SELECT id, estado, paterno, materno, nombre, seccion
        FROM {$map[$scope]} WHERE 1=1";
$par = [];
if ($estado !== '') { $sql .= " AND estado = :edo"; $par[':edo'] = mb_strtoupper($estado,'UTF-8'); }
if ($q !== '')       { $sql .= " AND (nombre LIKE :q OR paterno LIKE :q OR materno LIKE :q)"; $par[':q']="%$q%"; }
$sql .= " ORDER BY estado, paterno, materno, nombre LIMIT 20";

$st = $pdo->prepare($sql); $st->execute($par);
$rows = $st->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(array_map(fn($r)=>[
  'id'    => $r['id'],
  'label' => "{$r['nombre']} {$r['paterno']} {$r['materno']} — {$r['estado']} (Sec. {$r['seccion']})"
], $rows));
