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
set_cache_headers('html-nocache');
render_flash_swals();

$pageTitle = 'Resumen de registros';
$pageHeader = 'Dashboard OCR';

$customScripts = [];
// SweetAlert
$sweetPath = ASSETS_DIR . '/js/sweetalert2-v11.26.18.js';
$sweetVersion = file_exists($sweetPath) ? filemtime($sweetPath) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/sweetalert2-v11.26.18.js?v='.$sweetVersion.'"></script>';
$customScripts[] =
  '<script src="assets/js/dashboard.js"></script>';

require_once path_join(APP_ROOT, 'includes', 'header.php');
?>

<div class="container">
  <div class="d-flex flex-wrap justify-content-center gap-3" id="resumenCards">
    <div class="container d-flex justify-content-center flex-wrap gap-3 my-4" id="cardsContainer">
    <div class="card text-bg-primary shadow" style="width: 16rem;">
        <div class="card-body">
        <h5 class="card-title">Total</h5>
        <p class="card-text display-6" id="totalCard">...</p>
        </div>
    </div>

    <div class="card text-bg-success shadow" style="width: 16rem;">
        <div class="card-body">
        <h5 class="card-title">CURPs válidos</h5>
        <p class="card-text display-6" id="validosCard">...</p>
        </div>
    </div>

    <div class="card text-bg-danger shadow" style="width: 16rem;">
            <div class="card-body">
            <h5 class="card-title">CURPs inválidos</h5>
            <p class="card-text display-6" id="invalidosCard">...</p>
            </div>
        </div>
    </div>
    <div class="card text-bg-warning shadow" style="width: 16rem;">
        <div class="card-body">
            <h5 class="card-title">Secciones únicas</h5>
            <p class="card-text display-6" id="seccionesCard">...</p>
        </div>
    </div>
  </div>
</div>

<?php require_once path_join(APP_ROOT, 'includes', 'footer.php'); ?>
