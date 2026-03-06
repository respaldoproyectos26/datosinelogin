<?php
// ================================
// prefix.php SOLO USA BASE_URL
// ================================
if (!defined('BASE_URL')) {
    die("ERROR: config.php debe cargarse antes que prefix.php");
}

define('FORCE_API', false);

// ================================
// ASSETS
// ================================
define('CSS_URL', BASE_URL . '/assets/css');
define('JS_URL',  BASE_URL . '/assets/js');
define('IMG_URL', BASE_URL . '/assets/img');

// ================================
// MÓDULOS PRINCIPALES
// ================================
define('ACEPTACION_URL',     BASE_URL . '/aceptacion');
define('CARGA_CSV_URL',      BASE_URL . '/carga_csv');
define('CASILLAS_URL',       BASE_URL . '/casillas');
define('CASILLAS_API_URL',   BASE_URL . '/casillas/api');
define('FUERZAS_URL',        BASE_URL . '/fuerzas');
define('FUERZAS2_URL',       BASE_URL . '/fuerzas2');
define('GEOLOC_URL',         BASE_URL . '/geolocalizador');
define('TABLERO_URL',        BASE_URL . '/tablero');
define('USUARIOS_URL',       BASE_URL . '/usuarios');

// ================================
// INFORMACIÓN (info.*)
// ================================
define('INFO_COL_URL',             BASE_URL . '/infocolonias');
define('INFO_COL_NEW_URL',         BASE_URL . '/infocoloniasnew');
define('INFO_COL_UNICAS_URL',      BASE_URL . '/infocoloniasunicas');
define('INFO_DISTLOC2_URL',        BASE_URL . '/infodistloc2nau');
define('INFO_DISTLOC_URL',         BASE_URL . '/infodistlocnau');
define('INFO_EJIDOS_URL',          BASE_URL . '/infoejidos');
define('INFO_FRAC_CAMP_URL',       BASE_URL . '/infofraccamp');
define('INFO_FRAC_INDUST_URL',     BASE_URL . '/infofraccindust');
define('INFO_FRAC_RESID_URL',      BASE_URL . '/infofraccresid');
define('INFO_LOCALIDADES_URL',     BASE_URL . '/infolocalidades');
define('INFO_PUEBLOS_URL',         BASE_URL . '/infopueblos');

// ================================
// FORMULARIOS
// ================================
define('FORM_DISTFED_URL',      BASE_URL . '/formDistFed');
define('FORM_DISTFEDNEW_URL',   BASE_URL . '/formDistFednew');
define('FORM_DISTLOC_URL',      BASE_URL . '/formDistLoc');
define('FORM_DISTLOCNEW_URL',   BASE_URL . '/formDistLocnew');
define('FORM_ESTATAL_URL',      BASE_URL . '/formEstatal');
define('FORM_ESTATALNEW_URL',   BASE_URL . '/formEstatalnew');
define('FORM_MUNICIPAL_URL',    BASE_URL . '/formMunicipal');
define('FORM_MUNICIPALNEW_URL', BASE_URL . '/formMunicipalnew');

define('FORM_DATOS1_URL', BASE_URL . '/formulario');
define('FORM_DATOS2_URL', BASE_URL . '/formulario2');
define('FORM_DATOS3_URL', BASE_URL . '/formulario3');

// ================================
// FILTROS
// ================================
define('FILT_DISTFED_URL',      BASE_URL . '/filtDistFed');
define('FILT_DISTFEDNEW_URL',   BASE_URL . '/filtDistFednew');
define('FILT_DISTLOC_URL',      BASE_URL . '/filtDistLoc');
define('FILT_DISTLOCNEW_URL',   BASE_URL . '/filtDistLocnew');
define('FILT_ESTATAL_URL',      BASE_URL . '/filtEstatal');
define('FILT_ESTATALNEW_URL',   BASE_URL . '/filtEstatalnew');
define('FILT_MUNICIPAL_URL',    BASE_URL . '/filtMunicipal');
define('FILT_MUNICIPALNEW_URL', BASE_URL . '/filtMunicipalnew');

define('FILT_DATOS1_URL', BASE_URL . '/filtrodatos');
define('FILT_DATOS2_URL', BASE_URL . '/filtrodatos2');
define('FILT_DATOS3_URL', BASE_URL . '/filtrodatos3');

// ================================
// REGISTROS
// ================================
define('REG_EST1_URL', BASE_URL . '/registroest');
define('REG_EST2_URL', BASE_URL . '/registroest2');
define('REG_EST3_URL', BASE_URL . '/registroest3');
define('REG_EST4_URL', BASE_URL . '/registroest4');
define('REG_EST5_URL', BASE_URL . '/registroest5');
define('REG_EST6_URL', BASE_URL . '/registroest6');
define('REG_EST7_URL', BASE_URL . '/registroest7');
define('REG_EST8_URL', BASE_URL . '/registroest8');
define('REG_EST9_URL', BASE_URL . '/registroest9');

// ================================
// DOCS
// ================================
define('DOCS_URL', BASE_URL . '/docs');

// ================================
// HELPERS
// ================================
define('HELPERS_URL', BASE_URL . '/helpers');

// ================================
// INCLUDES
// ================================
define('INCLUDES_URL', BASE_URL . '/includes');

// ==========================================
//  MAPA URL PARA pathUrl()
// ==========================================
$URLS = [

    // Assets
    'css' => CSS_URL,
    'js'  => JS_URL,
    'img' => IMG_URL,

    // Módulos principales
    'aceptacion' => ACEPTACION_URL,
    'carga_csv'  => CARGA_CSV_URL,
    'casillas'   => CASILLAS_URL,
    'fuerzas'    => FUERZAS_URL,
    'fuerzas2'   => FUERZAS2_URL,
    'geolocalizador' => GEOLOC_URL,
    'usuarios'   => USUARIOS_URL,
    'tablero'    => TABLERO_URL,

    // Información
    'info' => [
        'colonias'       => INFO_COL_URL,
        'coloniasNew'    => INFO_COL_NEW_URL,
        'coloniasUnicas' => INFO_COL_UNICAS_URL,
        'distLoc2'       => INFO_DISTLOC2_URL,
        'distLoc'        => INFO_DISTLOC_URL,
        'ejidos'         => INFO_EJIDOS_URL,
        'fraccCamp'      => INFO_FRAC_CAMP_URL,
        'fraccIndust'    => INFO_FRAC_INDUST_URL,
        'fraccResid'     => INFO_FRAC_RESID_URL,
        'localidades'    => INFO_LOCALIDADES_URL,
        'pueblos'        => INFO_PUEBLOS_URL,
    ],

    // Formularios
    'formularios' => [
        'distFed'      => FORM_DISTFED_URL,
        'distFedNew'   => FORM_DISTFEDNEW_URL,
        'distLoc'      => FORM_DISTLOC_URL,
        'distLocNew'   => FORM_DISTLOCNEW_URL,
        'estatal'      => FORM_ESTATAL_URL,
        'estatalNew'   => FORM_ESTATALNEW_URL,
        'municipal'    => FORM_MUNICIPAL_URL,
        'municipalNew' => FORM_MUNICIPALNEW_URL,
    ],

    // Filtros
    'filtros' => [
        'distFed'      => FILT_DISTFED_URL,
        'distFedNew'   => FILT_DISTFEDNEW_URL,
        'distLoc'      => FILT_DISTLOC_URL,
        'distLocNew'   => FILT_DISTLOCNEW_URL,
        'estatal'      => FILT_ESTATAL_URL,
        'estatalNew'   => FILT_ESTATALNEW_URL,
        'municipal'    => FILT_MUNICIPAL_URL,
        'municipalNew' => FILT_MUNICIPALNEW_URL,
        'datos1'       => FILT_DATOS1_URL,
        'datos2'       => FILT_DATOS2_URL,
        'datos3'       => FILT_DATOS3_URL,
    ],

    // Registros
    'registros' => [
        'est1' => REG_EST1_URL,
        'est2' => REG_EST2_URL,
        'est3' => REG_EST3_URL,
        'est4' => REG_EST4_URL,
        'est5' => REG_EST5_URL,
        'est6' => REG_EST6_URL,
        'est7' => REG_EST7_URL,
        'est8' => REG_EST8_URL,
        'est9' => REG_EST9_URL,
    ],
];

define('APP_URLS', $URLS);

function pathUrl($key) {
    $keys = explode('.', $key);
    $ref = APP_URLS;
    foreach ($keys as $k) {
        if (!isset($ref[$k])) return null;
        $ref = $ref[$k];
    }
    return $ref;
}

/*
Cómo usar pathUrl()
🔵 Para CSS
<link rel="stylesheet" href="<?= pathUrl('css') ?>/style.css">

🔵 Para imágenes
<img src="<?= pathUrl('img') ?>/logo.png">

🔵 Para enlaces de navegación
<a href="<?= pathUrl('fuerzas2') ?>">Ver Fuerzas 2</a>

🔵 Para info
<a href="<?= pathUrl('info.coloniasNew') ?>/mapa.php">Mapa</a>

🔵 Para formularios
<form action="<?= pathUrl('formularios.estatalNew') ?>/guardar.php">

🔵 Para redirecciones
header("Location: " . pathUrl('casillas') . "/index.php");
exit;

🔥 4. Beneficios

✔ TODO queda simétrico:
path() para PHP en disco
pathUrl() para HTML/JS en navegador

✔ Misma estructura de claves (info.colonias, formularios.distFed, etc.) en ambos sistemas.

✔ Se eliminan rutas quemadas y errores al cambiar de dominio.

✔ Compatible con localhost, pejm2025, immnau automáticamente.
*/

$CSS_BASE = CSS_URL;
$JS_BASE  = JS_URL;


function asset_version($filePath) {
    return file_exists($filePath) ? filemtime($filePath) : time();
}
/*
USO: $scriptVersion = asset_version(FILTRO_MUN_DIR . '/js/script.js');
$customScripts[] =
  '<script src="'.FILT_MUNICIPAL_URL.'/js/script.js?v='.$scriptVersion.'" defer></script>';
*/