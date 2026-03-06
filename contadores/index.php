<?php
require_once __DIR__ . '/../helpers/bootstrap.php';

require_login();
set_cache_headers('html-nocache');

$pageTitle  = 'Resumen de estructura';
$pageHeader = 'Dashboard Estructura';

$customStyles = [
  '<link rel="stylesheet" href="../assets/css/estilos.css?ver=<?= date('."'Ymd'".') ?>',
];

$customScripts = [
  '<script src="'.asset('assets/js/sweetalert2-v11.26.18.js').'"></script>',
  '<script src="js/script.js"></script>',
];

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');
?>

<!-- HERO -->
<section class="hero-dashboard container">
  <div class="hero-figure">
    <img src="../assets/img/estructura-hero.png" alt="Estructura" loading="lazy">
  </div>
  <div class="hero-copy">
    <h1>Estructura SLP</h1>
    <p class="hero-sub">
      Resumen general de RG, RC y Promovidos. Usa el menú para navegar y gestionar.
    </p>
  </div>
</section>

 <div class="container my-1">
  <div class="d-flex flex-wrap justify-content-center gap-3" id="cardsContainer"></div>
</div>

<?php require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php'); ?>