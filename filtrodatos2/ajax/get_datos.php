<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php'; // carga config, helpers, auth, cache

require_login();                  // usuario autenticado
set_cache_headers('api');         // cache para APIs autenticadas

// Obtener parámetros
$nombre   = trim($_POST['nombre']   ?? '');
$paterno  = trim($_POST['paterno']  ?? '');
$materno  = trim($_POST['materno']  ?? '');
$estado   = trim($_POST['estado']   ?? '');
$municalc = trim($_POST['municalc'] ?? '');
$seccion  = trim($_POST['seccion']  ?? '');

// SQL dinámico seguro
$sql = "SELECT id, nombre, paterno, materno, estado, municalc, seccion, foto 
        FROM registrofig2 
        WHERE 1=1";
$params = [];

# Filtros
if ($nombre !== '')   { $sql .= " AND nombre LIKE ?";   $params[] = "%$nombre%"; }
if ($paterno !== '')  { $sql .= " AND paterno LIKE ?";  $params[] = "%$paterno%"; }
if ($materno !== '')  { $sql .= " AND materno LIKE ?";  $params[] = "%$materno%"; }
if ($estado !== '')   { $sql .= " AND estado = ?";      $params[] = $estado; }
if ($municalc !== '') { $sql .= " AND municalc LIKE ?"; $params[] = "%$municalc%"; }
if ($seccion !== '')  { $sql .= " AND seccion = ?";     $params[] = $seccion; }

try {
    $db = db();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    json([
        'ok' => true,
        'data' => $data
    ]);
} catch (PDOException $e) {
    api_fail("Error en SQL: ".$e->getMessage());
} catch (Throwable $e) {
    api_fail("Error inesperado: ".$e->getMessage());
}