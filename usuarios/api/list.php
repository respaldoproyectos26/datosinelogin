<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
// require_permission('ver_usuarios');

$pdo = db();

$draw   = (int)($_POST['draw'] ?? 1);
$start  = max(0, (int)($_POST['start'] ?? 0));
$length = max(1, min(100, (int)($_POST['length'] ?? 10)));
$search = trim($_POST['search']['value'] ?? '');

$columns = [
  0 => 'id',
  1 => 'usuario',
  2 => 'estado',
  3 => 'created_at'
];

$orderColIdx = (int)($_POST['order'][0]['column'] ?? 0);
$orderDir    = (($_POST['order'][0]['dir'] ?? 'asc') === 'desc') ? 'DESC' : 'ASC';
$orderBy     = $columns[$orderColIdx] ?? 'id';

$where = " WHERE 1=1 ";
$params = [];

if ($search !== '') {
  $where .= " AND (usuario LIKE :s OR estado LIKE :s) ";
  $params[':s'] = "%$search%";
}

$total = (int)$pdo->query("SELECT COUNT(*) FROM ine_usuarios_sanluis")->fetchColumn();

$stmtF = $pdo->prepare("SELECT COUNT(*) FROM ine_usuarios_sanluis $where");
$stmtF->execute($params);
$filtered = (int)$stmtF->fetchColumn();

$sql = "SELECT id, usuario, estado, created_at
        FROM ine_usuarios_sanluis
        $where
        ORDER BY $orderBy $orderDir
        LIMIT :start, :len";

$stmt = $pdo->prepare($sql);
foreach ($params as $k=>$v) $stmt->bindValue($k, $v, PDO::PARAM_STR);
$stmt->bindValue(':start', $start, PDO::PARAM_INT);
$stmt->bindValue(':len', $length, PDO::PARAM_INT);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  'draw' => $draw,
  'recordsTotal' => $total,
  'recordsFiltered' => $filtered,
  'data' => $data
], JSON_UNESCAPED_UNICODE);
