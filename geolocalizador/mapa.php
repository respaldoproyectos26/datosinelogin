<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
require_once HELPERS_DIR . '/deny.php';
require_once HELPERS_DIR . '/security.php';
require_once CONFIG_DIR . '/keys.php';

set_cache_headers('html-nocache');
require_login();

#if (!tiene_permiso('ver_geolocalizacion')) exit('No autorizado.');
if (!tiene_permiso('ver_geolocalizacion')) {
  deny_access(
    'No tienes permisos para ver la geolocalización',
    'login.php'
  );
}

// CSS
$customStyles = [
  '<link rel="stylesheet" href="'.pathUrl('css').'/estilos.css?v='.filemtime(path_join(ASSETS_DIR,'css','estilos.css')).'">',
  '<link rel="stylesheet" href="' . pathUrl('geolocalizador') . '/css/geolocalizador.css?v=' .
    filemtime(path_join(APP_ROOT, 'geolocalizador', 'css', 'geolocalizador.css')) . '">',
];

$pageTitle = 'MAPA DE UBICACIONES';
$pageHeader = 'Georeferencia';

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');

// URL GeoJSON (sirve para JS)
$geojsonBase = rtrim(BASE_URL,'/') . '/geojson'; // ideal: crea una constante GEOJSON_URL
?>
<div class="container-fluid d-flex row mx-auto">
  <div class="container-fluid filtros-container">
    <h3 class="text-center mb-3">Mapa por Categoría</h3>

    <div class="row justify-content-center">
      <div class="col-md-6 mb-2">
        <label for="filtro_categoria" class="form-label">CATEGORÍA</label>
        <select id="filtro_categoria" class="form-select">
          <option value="">-- Todas las figuras --</option>
          <option value="REP ESTATAL">REP ESTATAL</option>
          <option value="REP MUNICIPAL">REP MUNICIPAL</option>
          <option value="REP DIST FED">REP DIST FED</option>
          <option value="REP DIST LOC">REP DIST LOC</option>
          <option value="RG">RG</option>
          <option value="RC">RC</option>
          <option value="PROMOVIDO">PROMOVIDO</option>
        </select>
      </div>

      <div class="col-md-6 mb-2 d-none">
        <label for="filtro_estatus" class="form-label">ESTATUS</label>
        <select id="filtro_estatus" class="form-select" onchange="mostrarPorFiltros()">
          <option value="">-- Estatus --</option>
          <option value="ACTIVO">ACTIVO</option>
          <option value="INACTIVO">INACTIVO</option>
        </select>
      </div>

      <div class="col-md-6 mb-2">
        <label for="filtro_estado" class="form-label">ESTADO</label>
        <select id="filtro_estado" class="form-select">
          <option value="" selected>Elegir estado</option>
          <option value="24">SAN LUIS POTOSÍ</option>
        </select>
      </div>

      <div class="col-md-6 mb-2">
        <label for="filtro_municipio" class="form-label">MUNICIPIO</label>
        <select id="filtro_municipio" class="form-select">
          <option value="">-- Todos los municipios --</option>
        </select>
      </div>

      <div class="col-md-6 mb-2">
        <label for="filtro_seccion" class="form-label">SECCIÓN</label>
        <select id="filtro_seccion" class="form-select">
          <option value="">-- Todas las secciones --</option>
        </select>
      </div>
    </div>

    <div class="row justify-content-center mt-2">
      <div class="col-md-12 mb-2 text-center">
        <button class="btn btn-secondary btn-sm" onclick="ocultarTodas()">Ocultar todas</button>
        <button class="btn btn-secondary btn-sm" onclick="mostrarTodas()">Mostrar todas</button>
      </div>
    </div>

    <div id="mapa"></div>
  </div>
</div>

<hr>

<!-- Base de geojson accesible para JS -->
<script>window.GEOJSON_BASE = <?= json_encode($geojsonBase) ?>;</script>

<!-- Tu JS: define window.initMap -->
<?php
$mapaVersion = filemtime(__DIR__ . '/js/mapa.js');
?>
<script src="<?= e(GEOLOC_URL) ?>/js/mapa.js?v=<?= $mapaVersion ?>" defer></script>

<!-- Clusterer -->
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js" defer></script>

<!-- Google Maps: SIN async, SOLO defer -->
<script
  src="https://maps.googleapis.com/maps/api/js?key=<?= e(MAPS_JS_KEY) ?>&v=weekly&libraries=marker&callback=initMap&loading=async"
  defer></script>

<?php
require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php');
?>




