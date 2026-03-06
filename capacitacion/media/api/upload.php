<?php
require_once "../../../helpers/db.php";
require_once "../../../helpers/auth.php";
require_once "../../../helpers/helpers_media.php";

if (!auth()->can('subir_material')) {
    echo json_encode(['ok'=>false,'error'=>'No autorizado']); exit;
}

$pdo = db();
$titulo = $_POST['titulo'];
$descripcion = $_POST['descripcion'] ?? null;
$categoria = $_POST['categoria'] ?? null;
$link = $_POST['link'] ?? null;

$archivo = null;
$tipo = null;

// Si viene archivo...
if (!empty($_FILES['archivo']['name'])) {

    $file = $_FILES['archivo'];
    $name = time() . "_" . basename($file['name']);
    $path = "../../../uploads/media/$name";

    if (!file_exists("../../../uploads/media")) {
        mkdir("../../../uploads/media", 0777, true);
    }

    if (move_uploaded_file($file['tmp_name'], $path)) {
        $archivo = $name;
        $tipo = $file['type'];
    } else {
        echo json_encode(['ok'=>false,'error'=>'Error subiendo archivo']);
        exit;
    }
}

// Si viene link...
if ($link) {
    if(!media_validate_link($link)) {
        echo json_encode(['ok'=>false,'error'=>'Enlace no permitido']);
        exit;
    }
    $archivo = $link;
    $tipo = "link";
}

if (!$archivo) {
    echo json_encode(['ok'=>false,'error'=>'Debes subir archivo o link']);
    exit;
}

$stmt = $pdo->prepare("
    INSERT INTO media (titulo, descripcion, archivo, tipo, categoria, creado_por)
    VALUES (?,?,?,?,?,?)
");
$stmt->execute([$titulo,$descripcion,$archivo,$tipo,$categoria,auth()->id()]);

echo json_encode(['ok'=>true]);
