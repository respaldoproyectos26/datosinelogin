<?php
require_once __DIR__ . '/helpers/bootstrap.php';
require_once HELPERS_DIR . '/security.php';

set_cache_headers('html-nocache');

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Tras login correcto:
#session_regenerate_id(true);

$ip = $_SERVER['REMOTE_ADDR']; // o puedes usar username para limitar por usuario

# if (!rateLimit($ip, 5, 600)) { // máximo 5 intentos cada 10 minutos
#     exit('Demasiados intentos, espera un momento y vuelve a intentar.');
# }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link rel="manifest" href="/datosinelogin/assets/manifest.json?v=20250829">
    <meta name="theme-color" content="#0d6efd">
    <link rel="icon" href="assets/img/share-ios-icon.svg">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/estilos.css?v=<?= date('Ymd') ?>">
    <link rel="stylesheet" href="assets/css/login.css?v=<?= date('Ymd') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

<!-- Botón de instalar (Android) -->
  <button id="installBtn" class="btn btn-success shadow-lg rounded-pill px-4 py-2 w-75"
    style="display:none; position:fixed; bottom:20px; right:20px; z-index:1000;">
    📲 Instalar App
  </button>

  <!-- Mensaje sugerencia iOS -->
  <div id="iosInstallTip" style="display:none; position:fixed; bottom:10px; left:20px; right:20px; background:#fff3cd; 
  color:#856404; padding:15px; border:1px solid #ffeeba; border-radius:10px; z-index:1000;">
    <span style="font-size: 0.8rem;">
      📱 Para instalar esta app:
      <br>
      1. Pulsa el botón <img src="assets/img/share-ios-icon.svg" alt="Compartir" style="height:1.2em; vertical-align:middle;"> (Compartir)
      <br>
      2. Luego elige <img src="assets/img/add-to-home-screen.svg" alt="Agregar a pantalla de inicio" style="height:1.2em; 
      vertical-align:middle;"> (Agregar a pantalla de inicio)
    </span>
    <button onclick="this.parentElement.style.display='none'" class="btn btn-sm btn-light float-end mt-2">❌ Cerrar</button>
  </div>

<!-- Video de fondo -->
<video autoplay muted loop playsinline id="bg-video">
  <source src="assets/video/fondo.mp4" type="video/mp4">
</video>

<!-- Oscurecer fondo para contraste -->
<div class="overlay"></div>

<!-- Formulario -->
<div class="container">
  <h3>Login</h3>
  <form method="POST" action="check_login.php">
    <!-- Campos -->
    <div class="input-field">
      <input type="text" class="text-center" name="usuario" id="usuario" required>
      <label for="usuario">Usuario</label>
    </div>

    <div class="input-field">
      <input type="password" class="text-center" name="password" id="password" required>
      <label for="password">Contraseña</label>
    </div>

    <?php csrf_input(); ?>

    <button type="submit">Entrar</button>
  </form>
</div>

<?php
require_once 'helpers/flash.php';
render_flash_swals();
?>

<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/datosinelogin/sw.js', { scope: '/datosinelogin/' })
    .then(reg => console.log('SW registrado', reg.scope))
    .catch(err => console.error('SW no se pudo registrar:', err));
}

 // Detectar Android y mostrar botón
let deferredPrompt;
function isIOS() {
  return /iPhone|iPad|iPod/.test(navigator.userAgent) && !window.MSStream;
}
function isMobile() {
  return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
}

window.addEventListener('beforeinstallprompt', (e) => {
  if (!isMobile() || isIOS()) return;

  e.preventDefault();
  deferredPrompt = e;

  const installBtn = document.getElementById('installBtn');
  installBtn.style.display = 'block';

  installBtn.addEventListener('click', () => {
    installBtn.style.display = 'none';
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(choice => {
      if (choice.outcome === 'accepted') {
        console.log('✅ Usuario aceptó instalar la app');
      } else {
        console.log('❌ Usuario rechazó la instalación');
      }
      deferredPrompt = null;
    });
  });
});

// Sugerencia para iOS si no está instalada como app
if (isIOS() && !window.navigator.standalone) {
  document.getElementById('iosInstallTip').style.display = 'block';
}

window.addEventListener('appinstalled', () => {
  console.log('✅ App instalada');
});
</script>
</body>
</html>