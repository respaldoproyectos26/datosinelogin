<?php
/*
👉 Cuando NO quieres redirigir, sino mostrar una página final
(ej: usuario logueado pero sin rol).

Ejemplo ideal:
Panel admin
Error 403 real
Sin volver al login
*/

require_once __DIR__ . '/../helpers/bootstrap.php';
require_once HELPERS_DIR . '/url.php'; 

require_login();
set_cache_headers('html-nocache');
render_flash_swals();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Acceso denegado</title>
<link rel="icon" href="<?= asset('assets/img/share-ios-icon.svg') ?>">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
  body {
    background: #0e1320; 
    margin: 0; padding: 0;
    font-family: sans-serif;
  }
</style>
</head>
<body>

<script>
Swal.fire({
  icon: 'error',
  title: 'Acceso denegado',
  text: 'Tu sesión no tiene permisos para acceder a esta sección',
  confirmButtonText: 'Regresar'
}).then(() => {
  window.location.href = "<?= app_url('bienvenida.php') ?>";
});
</script>

</body>
</html>
