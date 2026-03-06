<?php
require_once __DIR__ . '/../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();

$pdo=db();

// Mapeo de columnas seguras (índice -> nombre real en SQL)
$columns = [
  0 => 'id_contador',
  1 => 'i.usuario',
  2 => 'ru.role_id',
  3 => 'r.name',
  4 => 'r.label',
  5 => 'p.id',
  6 => 'p.name',
  7 => 'p.label',
];

// Parámetros DataTables
$draw        = isset($_POST['draw'])  ? (int)$_POST['draw']  : 1;
$start       = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length      = isset($_POST['length'])? (int)$_POST['length']: 10;
$searchValue = $_POST['search']['value'] ?? '';

$orderColIdx = (int)($_POST['order'][0]['column'] ?? 0);
$orderDir    = ($_POST['order'][0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
$orderBy     = $columns[$orderColIdx] ?? $columns[0];

// Filtros por columna
$columnFilters = [];
$params = [];

// Si llega estructura columns de DataTables
if (!empty($_POST['columns']) && is_array($_POST['columns'])) {
  foreach ($_POST['columns'] as $i => $col) {
    $val = trim($col['search']['value'] ?? '');
    if ($val !== '' && isset($columns[$i])) {
      $key = ":cf_$i";
      $columnFilters[] = "{$columns[$i]} LIKE $key";
      $params[$key] = "%$val%";
    }
  }
}

// Búsqueda global
$globalWhere = [];
if ($searchValue !== '') {
  foreach ($columns as $colName) {
    $globalWhere[] = "$colName LIKE :g";
  }
  $params[':g'] = "%$searchValue%";
}

// Construcción del WHERE
$whereParts = [];
if ($columnFilters) { $whereParts[] = '(' . implode(' AND ', $columnFilters) . ')'; }
if ($globalWhere)   { $whereParts[] = '(' . implode(' OR ' , $globalWhere)   . ')'; }
$where = $whereParts ? 'WHERE ' . implode(' AND ', $whereParts) : '';

// Base FROM con JOINs (reutilizable)
$FROM = "
  FROM ine_usuarios_sanluis i
  LEFT JOIN role_user ru ON i.id = ru.user_id
  LEFT JOIN roles r ON ru.role_id = r.id
  LEFT JOIN permission_role pr ON pr.role_id = r.id
  LEFT JOIN permissions p ON pr.permission_id = p.id
";

// Totales sin filtros
$sqlTotal = "SELECT COUNT(*) $FROM";
$totalRecords = (int)$pdo->query($sqlTotal)->fetchColumn();

// Totales con filtros
$sqlFiltered = "SELECT COUNT(*) AS total $FROM $where";
$stmtFiltered = $pdo->prepare($sqlFiltered);
$stmtFiltered->execute($params);
$recordsFiltered = (int)$stmtFiltered->fetchColumn();

// Datos paginados
$sqlData = "
  SELECT 
    ROW_NUMBER() OVER (ORDER BY r.name) AS id_contador,
    i.id AS usuario_id, 
    i.usuario, 
    ru.role_id, 
    r.name AS role_name, 
    r.label AS role_label, 
    p.id AS permiso_id, 
    p.name AS permiso_name, 
    p.label AS permiso_label
  $FROM
  $where
  ORDER BY $orderBy $orderDir, usuario_id ASC, permiso_id ASC, id_contador ASC
  LIMIT $start, $length
";
$stmtData = db()->prepare($sqlData);
$stmtData->execute($params);
$data = $stmtData->fetchAll();

// Respuesta
echo json_encode([
  'draw'            => $draw,
  'recordsTotal'    => $totalRecords,
  'recordsFiltered' => $recordsFiltered,
  'data'            => $data,
], JSON_UNESCAPED_UNICODE);
