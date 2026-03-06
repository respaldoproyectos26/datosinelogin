<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php'; // carga config, helpers, auth, cache

require_login();                  // usuario autenticado
set_cache_headers('api');         // cache para APIs autenticadas

$pdo = db();

try {
  // Si luego quieres filtros (estado/municalc/seccion), léelos de $_POST aquí.
  // Por ahora, conteos globales:

  $q = fn($sql)=> (int)$pdo->query($sql)->fetchColumn();

  $rg  = $q("SELECT COUNT(*) FROM registrofig");
  $rc  = $q("SELECT COUNT(*) FROM registrofig2");
  $pr  = $q("SELECT COUNT(*) FROM registrofig3");
  $tot = $rg + $rc + $pr;

  // Únicos a nivel global (unión de las 3)
  $secciones = $q("
    SELECT COUNT(*) FROM (
      SELECT DISTINCT seccion FROM registrofig
      UNION
      SELECT DISTINCT seccion FROM registrofig2
      UNION
      SELECT DISTINCT seccion FROM registrofig3
    ) t
  ");

  $municipios = $q("
    SELECT COUNT(*) FROM (
      SELECT DISTINCT municalc FROM registrofig
      UNION
      SELECT DISTINCT municalc FROM registrofig2
      UNION
      SELECT DISTINCT municalc FROM registrofig3
    ) t
  ");

  $estados = $q("
    SELECT COUNT(*) FROM (
      SELECT DISTINCT estado FROM registrofig
      UNION
      SELECT DISTINCT estado FROM registrofig2
      UNION
      SELECT DISTINCT estado FROM registrofig3
    ) t
  ");

  echo json_encode([
    'ok' => true,
    'rg' => $rg,
    'rc' => $rc,
    'prom' => $pr,
    'total' => $tot,
    'secciones' => $secciones,
    'municipios' => $municipios,
    'estados' => $estados,
  ]);

} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'Error','error'=>$e->getMessage()]);
}
