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

if (!tiene_permiso('ver_panel_roles')) {
    denegar_acceso();
}

// Guardar nuevo permiso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_check();
  $name  = trim($_POST['name'] ?? '');
  $label = trim($_POST['label'] ?? '');

  #$name = strtolower(trim($name));
  $name = preg_replace('/\s+/', '_', $name);

  if ($name !== '' && $label !== '') {
    try {
        $stmt = db()->prepare("INSERT INTO permissions (name, label) VALUES (?, ?)");
        $stmt->execute([$name, $label]);
        flash('success','Permiso creado');
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) == 1062) { // duplicate key MySQL
            flash('warning','Ese permiso ya existe');
        } else {
            flash('error','Error al crear permiso: '.$e->getMessage());
        }
    }
redirect('gestion_roles.php');
  }
}
?>

<?php require_once "includes/header.php"; ?>
<div class="container my-4 justify-content-center mx-auto my-auto">
    <h3>Agregar Permiso</h3>

    <form method="post" class="card p-3 bg-white shadow-sm">
        <div class="mb-3">
            <label class="form-label">Nombre del permiso</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción del permiso</label>
            <textarea name="label" id="label" class="form-control" required></textarea>
        </div>

        <button class="btn btn-primary">Guardar</button>
        <a href="visorreg/" class="btn btn-secondary">Regresar</a>
    </form>

</div>
<?php require_once "includes/footer.php"; ?>
