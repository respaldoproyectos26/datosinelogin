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

$customStyles = [
  '<link rel="stylesheet" href="assets/css/estilos.css">',
];

$customScripts = [];
// SweetAlert
$sweetPath = ASSETS_DIR . '/js/sweetalert2-v11.26.18.js';
$sweetVersion = file_exists($sweetPath) ? filemtime($sweetPath) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/sweetalert2-v11.26.18.js?v='.$sweetVersion.'"></script>';

$pageTitle  = 'Inicio';
$pageHeader = 'Estructura SLP';

require_once path_join(APP_ROOT, 'includes', 'header.php');
?>

<section class="hero-dashboard container">
  <div class="hero-figure">
    <img src="assets/img/estructura-hero.png" alt="Estructura" loading="lazy">
  </div>
  <div class="hero-copy">
    <h1>Estructura SLP</h1>
    <p class="hero-sub">Bienvenido. Usa el menú lateral para registrar y consultar RG, RC y Promovidos.</p>
  </div>
</section>

<div class="container my-0">
  <!-- Banner con imagen -->
  <div class="alert alert-secondary banner-centered has-bg text-white" role="alert">
    <img src="assets/img/fondoInicio.png" class="img-fluid mx-auto d-block" alt="">
  </div>

  <!-- Banner de texto -->
  <div class="alert alert-secondary" role="alert">
    <h5 class="mb-0"><i class="bi bi-info-circle"></i> 
      Puedes ir a <b>Reportes</b> para ver el resumen en tarjetas.
    </h5>
  </div>
</div>

<?php require_once path_join(APP_ROOT, 'includes', 'footer.php'); ?>
