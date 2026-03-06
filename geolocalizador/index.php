<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
require_once HELPERS_DIR . '/deny.php';
require_once HELPERS_DIR . '/security.php';

require_login();
set_cache_headers('html-nocache');

// (opcional) permisos
#require_permission('ver_geolocalizacion');
if (!tiene_permiso('ver_geolocalizacion')) {
  deny_access('No tienes permisos para ver la geolocalización','login.php');
}

$customStyles = [
    '<link rel="stylesheet" href="'.pathUrl('css').'/estilos.css?v='.filemtime(path_join(ASSETS_DIR,'css','estilos.css')).'">',
    '<link rel="stylesheet" href="'.pathUrl('geolocalizador').'/css/form.css?v='.filemtime(path_join(APP_ROOT,'geolocalizador','css','form.css')).'">',
];

$pageTitle  = 'Guardar registro estructura';
$pageHeader = 'Guardar Registro Estructura SLP';

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');
require_once path_join(APP_ROOT, 'includes', 'fieldset.php');
?>

<div class="container mt-4">
  <h4>Registrar Categoría Manual</h4>
  <form id="formCategoria">
    <div class="row">
      <?php
      $fields = [
        'direccion', 'consecutivo', 'clave_catastral', 'domicilio', 'nombre', 'rfc', 'status',
        'valor_catastral', 'supterreno', 'supconstruccion', 'descripcion', 'corriente', 'rezago',
        'importe', 'nuevo_no', 'familia', 'clave', 'cic', 'ocr', 'vigencia', 'curp', 'dia', 'mes',
        'anio', 'codigo_postal', 'seccion', 'region', 'telefono', 'facebook', 'parentesco',
        'movilizador', 'enlace', 'programa', 'descripcion2', 'ctto', 'fecha_elaboracion',
        'busqueda_registro', 'localidad', 'municipio', 'estado', 'cp', 'nombre_razon_social',
        'giro', 'direccion_comercio', 'numero_comercio', 'colonia_comercio', 'localidad_comercio',
        'cp_comercio', 'movimiento', 'fecha', 'genero', 'edad', 'documentos', 'canalizados_empresas',
        'entrevista', 'estudios', 'programa_abriendo_espacios', 'aun_en_espera'
      ];


      foreach ($fields as $field) {
        echo "<div class='col-md-4 mb-3'>
                <label>" . ucwords(str_replace(['_', 'cp'], [' ', 'C.P.'], $field)) . ":</label>
                <input type='text' class='form-control' name='$field'>
              </div>";
      }
      ?>
    </div>
    <button type="submit" class="btn btn-success">Guardar</button>
  </form>
</div>

<script>
  document.getElementById('formCategoria').addEventListener('submit', async function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const response = await fetch('api/guardar_categoria.php', {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrfToken() }, // o _csrf en FormData
      body: formData,
      credentials: 'same-origin'
    });
    const result = await response.json();
    if (result.success) {
      alert('Registro guardado correctamente');
      this.reset();
    } else {
      alert('Error: ' + result.message);
    }
  });
</script>

<?php
require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php');
?>
