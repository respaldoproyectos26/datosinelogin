<?php
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../helpers/response.php';

require_login(); // ← si quieres protegerlo

$db = db();

$total = $db->query("SELECT COUNT(*) FROM ine_ocr")->fetchColumn();
$validos = $db->query("SELECT COUNT(*) FROM ine_ocr WHERE LENGTH(curp) = 18")->fetchColumn();
$invalidos = $total - $validos;
$unicos_seccion = $db->query("SELECT COUNT(DISTINCT seccion) FROM ine_ocr")->fetchColumn();

json([
  'total' => (int)$total,
  'curps_validos' => (int)$validos,
  'curps_invalidos' => (int)$invalidos,
  'secciones' => (int)$unicos_seccion
]);

