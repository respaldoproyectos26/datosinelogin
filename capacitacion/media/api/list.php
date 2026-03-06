<?php
require_once "../../../helpers/db.php";

$pdo = db();
$rows = $pdo->query("SELECT id,titulo,descripcion,archivo,tipo,categoria,creado_en FROM media ORDER BY creado_en DESC")
            ->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['dt'])) {
  // DataTables expects { data: [...] }
  echo json_encode(['data'=>$rows]);
} else {
  echo json_encode($rows);
}
