<?php
// helpers/errores.php
// Configuración global de errores y salida

// Define entorno (puedes definir APP_ENV=prod en tu .env o en config.php)
# $env = getenv('APP_ENV') ?: 'dev';
$env = defined('APP_ENV') ? APP_ENV : (getenv('APP_ENV') ?: 'dev');

// Configura nivel de errores según entorno
if ($env === 'prod') {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1); // <-- se incluye aquí
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php_errors_dev.log');
}

// Cabeceras de seguridad básicas (no rompen nada existente)
if (!headers_sent()) {
  header('X-Content-Type-Options: nosniff');
  header('X-Frame-Options: SAMEORIGIN');
  header('Referrer-Policy: strict-origin-when-cross-origin');
  // Si sirves todo por HTTPS, descomenta:
  // header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}