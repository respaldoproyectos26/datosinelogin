<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
require_once HELPERS_DIR . '/deny.php';

set_cache_headers('html-nocache');
require_login();

#if (!tiene_permiso('ver_geolocalizacion')) exit('No autorizado.');
if (!tiene_permiso('ver_geolocalizacion')) {
  deny_access(
    'No tienes permisos para ver la geolocalización',
    'login.php'
  );
}

$customStyles = [
  '<link rel="stylesheet" href="'.pathUrl('css').'/estilos.css">',
  '<link rel="stylesheet" href="'.GEOLOC_URL.'/css/geolocalizador.css">'
];
$customScripts = [
  '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>',
];

$pageTitle = 'Carga masiva de ubicaciones de usuarios';
$pageHeader = 'Geolocalización Masiva';

require_once path_join(APP_ROOT,'includes','headerfiltrodatos.php');
?>

<div class="container mt-4">
  <h2 class="mb-4">Carga Masiva de Registros</h2>
  <form id="formCargaCSV" enctype="multipart/form-data" method="POST">
    <div class="mb-3">
      <label for="archivo_csv" class="form-label">Selecciona archivo CSV</label>
      <input class="form-control" type="file" name="archivo_csv" id="archivo_csv" accept=".csv" required>
    </div>
    <button type="submit" class="btn btn-primary">Subir y procesar</button>
  </form>
</div>

<script>
  document.getElementById('formCargaCSV').addEventListener('submit', async function (e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);

  try {
    const r = await fetch('api/carga_masiva.php', {
      method:'POST',
      headers: { 'X-CSRF-Token': csrfToken() },
      body: fd,
      credentials:'same-origin'
    });

    const data = await r.json();
    console.log(data);

    if (data.success) {
      Swal.fire('Éxito', data.message, 'success');
    } else {
      Swal.fire('Errores', data.errores.join('\n'), 'error');
    }
  } catch (err) {
    console.error('Error al enviar:', err);
    Swal.fire('Error', 'Hubo un problema al subir el archivo.', 'error');
  }
});
</script>

<?php
require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php');
?>