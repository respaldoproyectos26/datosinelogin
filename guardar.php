<?php
require_once 'helpers/errores.php';
require_once 'helpers/cache_headers.php';
require_once 'helpers/db.php';
require_once 'helpers/prefix.php';
require_once 'helpers/csrf.php';
require_once 'helpers/auth.php';
require_once 'helpers/url.php';
require_once 'helpers/flash.php';

require_login(); // esto valida correctamente que esté logueado
set_cache_headers('api');

// Lista de campos esperados
$campos = ['nombre_completo', 'curp', 'clave_elector', 'seccion', 'vigencia', 'emision', 'sexo', 'domicilio', 'anio_registro'];
$datos = [];
foreach ($campos as $campo) {
    $datos[$campo] = trim($_POST[$campo] ?? '');
}

// Procesar imagen solo si no se mandó img_path directamente
$img_path = $_POST['img_path'] ?? '';

if (!$img_path && isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['foto']['tmp_name'];
    $nombre = uniqid('ocr_') . '.jpg';
    $destino = __DIR__ . '/uploads/' . $nombre;

    if (!move_uploaded_file($tmp, $destino)) {
        echo json_encode([
            'ok' => false,
            'msg' => 'No se pudo mover la imagen',
            'error' => error_get_last()
        ]);
        exit;
    }

    $img_path = 'uploads/' . $nombre;
}

// Insertar en la base de datos
$stmt = db()->prepare("
    INSERT INTO registros_ocr (
        nombre_completo, curp, clave_elector, seccion, vigencia, emision,
        sexo, domicilio, anio_registro, img_path
    ) VALUES (
        :nombre_completo, :curp, :clave_elector, :seccion, :vigencia, :emision,
        :sexo, :domicilio, :anio_registro, :img_path
    )
");

$ok = $stmt->execute([
    ':nombre_completo' => $datos['nombre_completo'],
    ':curp'            => $datos['curp'],
    ':clave_elector'   => $datos['clave_elector'],
    ':seccion'         => $datos['seccion'],
    ':vigencia'        => $datos['vigencia'],
    ':emision'         => $datos['emision'],
    ':sexo'            => $datos['sexo'],
    ':domicilio'       => $datos['domicilio'],
    ':anio_registro'   => $datos['anio_registro'],
    ':img_path'        => $img_path
]);

echo json_encode([
    'ok' => $ok,
    'msg' => $ok ? 'Registro guardado correctamente' : 'Error al guardar',
    'img_path' => $img_path,
    'errorInfo' => !$ok ? $stmt->errorInfo() : null
]);
