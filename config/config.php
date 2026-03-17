<?php
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);

if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)
{
    define('BASE_URL', '/elec2025/datosinelogin');
} else {
    define('BASE_URL', '/datosinelogin');
}

// SOLO TRUE si estás en HTTPS
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
ini_set('session.cookie_secure', $https);

session_set_cookie_params([
  'lifetime' => 0,
  'path' => rtrim(BASE_URL,'/') . '/',
  'domain' => '',
  'secure' => $https,
  'httponly' => true,
  #'samesite' => 'Strict'
  'samesite' => 'Lax'
]);

# ✅ session name por proyecto, único por módulo
$sessionName = 'DATOSINELOGIN_LDNL';
session_name($sessionName);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ENV (dev/prod) por host
if (!defined('APP_ENV')) {
  define('APP_ENV', (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) ? 'dev' : 'prod');
}

if (!defined('APP_DEBUG')) {
  $host = $_SERVER['HTTP_HOST'] ?? '';
  $isLocal = ($host === 'localhost' || strpos($host, '127.') === 0 || strpos($host, 'localhost') !== false);
  define('APP_DEBUG', $isLocal);
}

define('DB_DSN',  'mysql:host=localhost;dbname=elec2025;charset=utf8mb4');
define('DB_USER', 'root');
//define('DB_PASS', ''); // <-- localhost
define('DB_PASS', 'Pvem_2@26'); // <-- producción

/*
Generar un hash (fingerprint) usando:
El User-Agent del navegador
El ID de sesión (session_id())
Almacena ese hash en la sesión cuando se crea
En cada nueva petición, vuelve a calcular el hash y lo compara con el almacenado.
Si no coinciden, asume que la sesión fue secuestrada → se destruye.
*/

# APP_KEY y fingerprint siguen igual
# define('APP_KEY', 'abelhermite9710279admin');

# if (!isset($_SESSION['fingerprint'])) {
#   $_SESSION['fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . session_id());
# } else {
#   $fp = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . session_id());
#   if ($fp !== $_SESSION['fingerprint']) {
#     session_destroy();
#     exit('Sesión inválida');
#   }
# }

// Legacy: gate a proyecto austero (apagado por config.php)
define('USAR_PROYECTO_AUSTERO', false);

// Rutas absolutas seguras
define('CONFIG_DIR', __DIR__);
define('APP_ROOT', realpath(CONFIG_DIR . '/..'));   // raíz del proyecto
define('HELPERS_DIR', APP_ROOT . '/helpers');
define('INCLUDES_DIR', APP_ROOT . '/includes');

define('ASSETS_DIR', APP_ROOT . '/assets');
define('UPLOADS_DIR', APP_ROOT . '/uploads');
define('SQL_DIR', APP_ROOT . '/sql');
define('API_DIR', APP_ROOT . '/api');
define('GEO_DIR', APP_ROOT . '/geolocalizador');
define('CONTADORES_DIR', APP_ROOT . '/contadores');
define('VISRREQ_DIR', APP_ROOT . '/visrreq');

// Formularios específicos por nombre
define('FORM_DISTFED_DIR', APP_ROOT . '/formDistFed');
define('FORM_DISTFEDNEW_DIR', APP_ROOT . '/formDistFednew');
define('FORM_DISTLOC_DIR', APP_ROOT . '/formDistLoc');
define('FORM_DISTLOCNEW_DIR', APP_ROOT . '/formDistLocnew');
define('FORM_ESTATAL_DIR', APP_ROOT . '/formEstatal');
define('FORM_ESTATALNEW_DIR', APP_ROOT . '/formEstatalnew');
define('FORM_MUNICIPAL_DIR', APP_ROOT . '/formMunicipal');
define('FORM_MUNICIPALNEW_DIR', APP_ROOT . '/formMunicipalnew');
define('FORM_DIR', APP_ROOT . '/formulario');
define('FORM2_DIR', APP_ROOT . '/formulario2');
define('FORM3_DIR', APP_ROOT . '/formulario3');

// Filtros (puedes crear un array si lo prefieres)
define('FILTRO_FE_DIR', APP_ROOT . '/filtDistFed');
define('FILTRO_FE_NEW_DIR', APP_ROOT . '/filtDistFednew');
define('FILTRO_LO_DIR', APP_ROOT . '/filtDistLoc');
define('FILTRO_LO_NEW_DIR', APP_ROOT . '/filtDistLocnew');
define('FILTRO_ESTATAL_DIR', APP_ROOT . '/filtEstatal');
define('FILTRO_ESTATALNEW_DIR', APP_ROOT . '/filtEstatalnew');
define('FILTRO_MUN_DIR', APP_ROOT . '/filtMunicipal');
define('FILTRO_MUN_NEW_DIR', APP_ROOT . '/filtMunicipalnew');
define('FILTRO_DATOS_DIR', APP_ROOT . '/filtrodatos');
define('FILTRO_DATOS2_DIR', APP_ROOT . '/filtrodatos2');
define('FILTRO_DATOS3_DIR', APP_ROOT . '/filtrodatos3');

define('FILTROS_DIRS', [
    'distFed' => APP_ROOT . '/filtDistFed',
    'distFedNew' => APP_ROOT . '/filtDistFednew',
    'distLoc' => APP_ROOT . '/filtDistLoc',
    'distLocNew' => APP_ROOT . '/filtDistLocnew',
    'estatal' => APP_ROOT . '/filtEstatal',
    'estatalNew' => APP_ROOT . '/filtEstatalnew',
    'municipal' => APP_ROOT . '/filtMunicipal',
    'municipalNew' => APP_ROOT . '/filtMunicipalnew',
    'datos1' => APP_ROOT . '/filtrodatos',
    'datos2' => APP_ROOT . '/filtrodatos2',
    'datos3' => APP_ROOT . '/filtrodatos3',
]);

# Ejemplo: require_once FILTROS_DIRS['municipal'] . '/procesar.php';