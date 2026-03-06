<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
require_once HELPERS_DIR . '/deny.php';
require_once HELPERS_DIR . '/security.php';

set_cache_headers('html-nocache');
require_login();

#if (!tiene_permiso('ver_geolocalizacion')) exit('No autorizado.');
if (!tiene_permiso('ver_geolocalizacion')) {
  deny_access(
    'No tienes permisos para ver la geolocalización',
    'login.php'
  );
}

$pageTitle  = 'Guardar registro estructura SLP';
$pageHeader = '';

require_once path_join(APP_ROOT, 'includes', 'header.php');
render_flash_swals();
?>

<div class="container mt-5">
  <h3>Registrar usuario con geolocalización</h3>
  <form action="procesar_individual.php" method="POST">
    <div class="row">
      <div class="col-md-4 mb-3">
        <label>Nombre:</label>
        <input type="text" name="nombre" class="form-control" required>
      </div>
      <div class="col-md-4 mb-3">
        <label>Apellido paterno:</label>
        <input type="text" name="paterno" class="form-control">
      </div>
      <div class="col-md-4 mb-3">
        <label>Apellido materno:</label>
        <input type="text" name="materno" class="form-control">
      </div>

      <div class="col-md-6 mb-3">
        <label>Calle:</label>
        <input type="text" name="calle" class="form-control" required>
      </div>
      <div class="col-md-2 mb-3">
        <label>Num. Ext:</label>
        <input type="text" name="numext" class="form-control">
      </div>
      <div class="col-md-2 mb-3">
        <label>Num. Int:</label>
        <input type="text" name="numint" class="form-control">
      </div>
      <div class="col-md-2 mb-3">
        <label>Colonia:</label>
        <input type="text" name="colonia" class="form-control" required>
      </div>

      <div class="col-md-3 mb-3">
        <label>CP:</label>
        <input type="text" name="cp" class="form-control" required>
      </div>
      <div class="col-md-3 mb-3">
        <label>Municipio:</label>
        <input type="text" name="municipio" class="form-control" required>
      </div>

      <div class="col-md-6 mb-3">
        <label>Estructura:</label>
        <select name="categoria" class="form-select">
          <option value="MUNICIPAL">REP. MUNICIPAL</option>
          <option value="FEDERAL">REP. FEDERAL</option>
          <option value="DISTRITAL LOCAL">REP. DISTRITAL LOCAL</option>
          <option value="DISTRITAL FEDERAL">REP. DISTRITAL FEDERAL</option>
          <option value="RG">RG</option>
          <option value="RC">RC</option>
          <option value="PROMOVIDO">PROMOVIDO</option>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-success">Guardar registro</button>
  </form>
</div>

<?php
require_once path_join(APP_ROOT, 'includes', 'footer.php');
?>
