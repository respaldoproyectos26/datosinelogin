<?php
require_once __DIR__ . '/../../../helpers/bootstrap.php';
require_login(); set_cache_headers('api');

$rows = db()->query("SELECT id,titulo,descripcion,archivo,tipo,categoria,creado_en FROM media ORDER BY creado_en DESC")
            ->fetchAll(PDO::FETCH_ASSOC);

echo isset($_GET['dt']) ? json_encode(['data'=>$rows]) : json_encode($rows);
