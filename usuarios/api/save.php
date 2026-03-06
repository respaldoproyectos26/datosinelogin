<?php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
set_cache_headers('api');

require_login();
csrf_check();

// si ya tienes permisos:
// if ((int)($_POST['id'] ?? 0) > 0) require_permission('editar_usuarios'); else require_permission('crear_usuarios');

$id      = (int)($_POST['id'] ?? 0);
$usuario = trim($_POST['usuario'] ?? '');
$estado  = trim($_POST['estado'] ?? '');
$pass    = (string)($_POST['password'] ?? '');

if ($usuario === '' || $estado === '') {
  json(['success'=>false,'message'=>'Usuario y Estado son requeridos'], 422);
}
if (mb_strlen($estado) > 25) {
  json(['success'=>false,'message'=>'Estado máximo 25 caracteres'], 422);
}

$pdo = db();

try {
  if ($id > 0) {
    $pdo->beginTransaction();

    // Validar duplicado de usuario (excluyendo el mismo id)
    $st = $pdo->prepare("SELECT id FROM ine_usuarios_sanluis WHERE usuario=? AND id<>? LIMIT 1");
    $st->execute([$usuario, $id]);
    if ($st->fetchColumn()) {
      $pdo->rollBack();
      json(['success'=>false,'message'=>'Ese usuario ya existe'], 409);
    }

    $pdo->prepare("UPDATE ine_usuarios_sanluis SET usuario=?, estado=? WHERE id=?")
        ->execute([$usuario, $estado, $id]);

    if ($pass !== '') {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $pdo->prepare("UPDATE ine_usuarios_sanluis SET pass_hash=? WHERE id=?")
          ->execute([$hash, $id]);
    }

    $pdo->commit();
    json(['success'=>true,'message'=>'Usuario actualizado']);
  }

  // Crear
  if ($pass === '') json(['success'=>false,'message'=>'Password requerido al crear'], 422);

  // Validar duplicado de usuario
  $st = $pdo->prepare("SELECT id FROM ine_usuarios_sanluis WHERE usuario=? LIMIT 1");
  $st->execute([$usuario]);
  if ($st->fetchColumn()) {
    json(['success'=>false,'message'=>'Ese usuario ya existe'], 409);
  }

  $hash = password_hash($pass, PASSWORD_DEFAULT);
  $pdo->prepare("INSERT INTO ine_usuarios_sanluis (usuario, estado, pass_hash) VALUES (?, ?, ?)")
      ->execute([$usuario, $estado, $hash]);

  json(['success'=>true,'message'=>'Usuario creado']);
} catch (Throwable $e) {
  error_log("usuarios save error: ".$e->getMessage());
  json(['success'=>false,'message'=>'Error al guardar'], 500);
}
