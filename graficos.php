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

require_permission('ver_graficos');

$pageTitle = 'Gráficos';
$pageHeader = 'Visualización de Gráficos';

$customScripts = [];
// SweetAlert
$sweetPath = ASSETS_DIR . '/js/sweetalert2-v11.26.18.js';
$sweetVersion = file_exists($sweetPath) ? filemtime($sweetPath) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/sweetalert2-v11.26.18.js?v='.$sweetVersion.'"></script>';
// Chart.js
$chartjsPath = ASSETS_DIR . '/js/Chart.js-v4.5.1.js';
$chartjsVersion = file_exists($chartjsPath) ? filemtime($chartjsPath) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/Chart.js-v4.5.1.js?v='.$chartjsVersion.'"></script>';
$customScripts[] ='<script src="assets/js/graficos.js"></script>';

require_once path_join(APP_ROOT, 'includes', 'header.php');
?>

<div class="container-fluid d-flex flex-column justify-content-center align-items-center" 
style="min-height: calc(100vh - 116px); padding-top: 0px; padding-bottom: 60px;">
  
  <div class="grafico-container w-100 px-2">
    <canvas id="graficoResumen" class="grafico-canvas"></canvas>
  </div>

  <div class="text-center mt-3">
    <button class="btn btn-secondary me-2" data-chart="barras">Barras</button>
    <button class="btn btn-success me-2" data-chart="lineas">Líneas</button>
    <button class="btn btn-warning" data-chart="pastel" id="btnPastel" style="display:none">Pastel</button>
  </div>

</div>

<?php require_once path_join(APP_ROOT, 'includes', 'footer.php'); ?>
