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

$pageTitle = "Resumen de Registros";
$pageHeader = "Dashboard de CURPs";
$extraScripts = ["assets/js/resumen.js"];
require_once 'includes/header.php';
?>

<div class="row g-4 text-center" id="resumen-cards">
  <div class="col-md-4">
    <div class="card shadow-sm border-primary">
      <div class="card-body">
        <h5 class="card-title">Total de registros</h5>
        <p class="display-6" id="total">--</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm border-success">
      <div class="card-body">
        <h5 class="card-title">CURPs válidos</h5>
        <p class="display-6 text-success" id="validos">--</p>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm border-danger">
      <div class="card-body">
        <h5 class="card-title">CURPs inválidos</h5>
        <p class="display-6 text-danger" id="invalidos">--</p>
      </div>
    </div>
  </div>
</div>

<?php require_once 'includes/footer.php'; ?>
