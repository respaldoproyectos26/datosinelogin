<?php
require_once '../helpers/errores.php';
require_once '../config/config.php';
require_once '../helpers/db.php';
require_once '../helpers/csrf.php';
require_once '../helpers/response.php';

$input = json_decode(file_get_contents('php://input'), true);

$page     = max(1, (int)($input['page'] ?? 1));
$size     = max(1, (int)($input['size'] ?? 20));
$sortCol  = $input['sortField'] ?? 'id';
$sortDir  = strtolower($input['sortDir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$filters  = $input['filters'] ?? [];

$allowedSort = ['id','nombre_completo','curp','clave_elector','seccion','vigencia','emision','created_at'];
if (!in_array($sortCol, $allowedSort, true)) {
  $sortCol = 'id';
}

$where = [];
$params = [];

// Filtros simples (ejemplo: filterModel de AG Grid)
foreach ($filters as $field => $filter) {
  // Simplificación: solo contains
  if (!empty($filter['filter'])) {
    $where[] = "$field LIKE :$field";
    $params[":$field"] = "%{$filter['filter']}%";
  }
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = db()->prepare("SELECT COUNT(*) FROM registros_ocr $whereSql");
$total->execute($params);
$totalCount = (int)$total->fetchColumn();

$offset = ($page - 1) * $size;
$sql = "SELECT * FROM registros_ocr $whereSql ORDER BY $sortCol $sortDir LIMIT :size OFFSET :offset";
$stmt = db()->prepare($sql);
foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
$stmt->bindValue(':size', $size, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

json([
  'rows'  => $rows,
  'total' => $totalCount
]);

