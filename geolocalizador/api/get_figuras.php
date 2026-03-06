<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('json-nocache');

require_login();
if (!tiene_permiso('ver_geolocalizacion')) json(['error'=>'NO_AUTH'], 403);

// Es POST → CSRF (aunque sea “lectura”, así lo estás llamando)
csrf_check();

$figs = [
  'REP ESTATAL','REP MUNICIPAL','REP DIST FED','REP DIST LOC','RG','RC','PROMOVIDO'
];

json(array_map(fn($v)=>['value'=>$v,'label'=>$v], $figs), 200);
/*
Sin json() del helper response.php es
echo json_encode(array_map(fn($v)=>['value'=>$v,'label'=>$v], $figs), JSON_UNESCAPED_UNICODE);
*/

