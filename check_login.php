<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/errores.php';
require_once __DIR__ . '/helpers/cache_headers.php';
require_once __DIR__ . '/helpers/db.php';
require_once __DIR__ . '/helpers/auth.php';
require_once __DIR__ . '/helpers/flash.php';
require_once __DIR__ . '/helpers/csrf.php';
require_once __DIR__ . '/helpers/response.php';
require_once __DIR__ . '/helpers/url.php';

set_cache_headers('api');
csrf_check();

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;

/*
$max_attempts = 5;
if ($_SESSION['login_attempts'] >= $max_attempts) {
  flash('error', 'Has superado el número máximo de intentos. Intenta más tarde.');
  redirect('login.php');
  exit;
}
*/

$usuario  = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

/*
function austero_base_url(): string {
  return preg_replace('~/datosinelogin$~', '/datosineloginaustero', BASE_URL);
}
*/

function is_admin_user(): bool {
  $rol = $_SESSION['user']['rol'] ?? '';
  return ($rol === 'admin');
}

if (login($usuario, $password)) {
  $_SESSION['login_attempts'] = 0;
  $_SESSION['usuario'] = $usuario;

  flash('success', 'Bienvenido');

  if (
      defined('USAR_PROYECTO_AUSTERO')
      && USAR_PROYECTO_AUSTERO === true
      && !is_admin_user()
  ) {
      redirect(austero_base_url() . '/bienvenida.php');
      exit;
  }

  // default: FULL
  redirect(BASE_URL . '/bienvenida.php');
  exit;
} else {
  $_SESSION['login_attempts']++;
  flash('error', 'Credenciales incorrectas');
  redirect('login.php');
  exit;
}

