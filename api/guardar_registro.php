<?php
require_once '../helpers/db.php';
require_once '../helpers/response.php';
require_once '../helpers/auth.php';
require_once '../helpers/csrf.php';

require_login();

$input = json_decode(file_get_contents("php://input"), true);
$db = db();

// Validar entrada mínima
if (!$input || empty($input['curp'])) {
  json(['ok' => false, 'msg' => 'Datos incompletos'], 400);
}

$stmt = $db->prepare("INSERT INTO ine_ocr 
  (img_path, clave_elector, curp, seccion, emision, vigencia, creado_en) 
  VALUES 
  (:img, :clave, :curp, :seccion, :emision, :vigencia, NOW())");

$ok = $stmt->execute([
  ':img'      => $input['img_path'] ?? '', // puede ser vacío si aún no manejas imagen
  ':clave'    => $input['clave_elector'] ?? '',
  ':curp'     => $input['curp'] ?? '',
  ':seccion'  => $input['seccion'] ?? '',
  ':emision'  => $input['emision'] ?? '',
  ':vigencia' => $input['vigencia'] ?? ''
]);

json(['ok' => $ok, 'msg' => $ok ? 'Registro guardado' : 'Error al guardar']);