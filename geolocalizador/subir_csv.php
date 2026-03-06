<?php
require_once '../includes/header.php';
require_once '../helpers/auth.php';
require_login();
if (!tiene_permiso('cargar_csv_geolocalizacion')) {
    exit('Acceso no autorizado.');
}
?>

<div class="container mt-5">
  <h3>Subir archivo CSV de usuarios y ubicaciones</h3>
  <form action="procesar_csv.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Archivo CSV:</label>
      <input type="file" name="archivo" class="form-control" required accept=".csv">
    </div>
    <div class="mb-3">
      <label class="form-label">Clave de seguridad:</label>
      <input type="password" name="clave" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Procesar</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>
