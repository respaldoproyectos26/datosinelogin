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

require_permission('ver_panel_roles');

$usuarios = db()->query("SELECT id, usuario FROM ine_usuarios_sanluis ORDER BY usuario")->fetchAll(PDO::FETCH_ASSOC);
$roles = db()->query("SELECT id, label FROM roles ORDER BY label")->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Gestión de Roles y Permisos';

$customStyles=[
  '<link rel="stylesheet" href="'.asset('assets/css/select2@4.1.0-rc.0.min.css').'">',
];

$customScripts = [];
// SweetAlert
$sweetPath = ASSETS_DIR . '/js/sweetalert2-v11.26.18.js';
$sweetVersion = file_exists($sweetPath) ? filemtime($sweetPath) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/sweetalert2-v11.26.18.js?v='.$sweetVersion.'"></script>';

// Select2
$select2Path = ASSETS_DIR . '/js/select2@4.1.0-rc.0.min.js';
$sweetVersion = file_exists($select2Path) ? filemtime($select2Path) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/select2@4.1.0-rc.0.min.js?v='.$sweetVersion.'"></script>';

require_once path_join(APP_ROOT, 'includes', 'header.php');
?>
<div class="container py-4">
  <h2 class="mb-4">Gestión de Roles y Permisos</h2>

  <div class="row">
    <!-- Panel de Usuario → Roles -->
    <div class="col-md-6 mt-4">
      <h4>Asignar Roles a Usuario</h4>
      <select id="usuarioSelect" class="form-select mb-3">
        <option value="">-- Selecciona un usuario --</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['usuario']) ?></option>
        <?php endforeach; ?>
      </select>

      <div id="rolesContainer" class="bg-white border rounded p-3">
        <em class="text-muted">Selecciona un usuario para ver sus roles.</em>
      </div>
    </div>

    <!-- Panel de Rol → Permisos -->
    <div class="col-md-6 mt-4">
      <h4>Asignar Permisos a Rol</h4>
      <select id="rolSelect" class="form-select mb-3">
        <option value="">-- Selecciona un rol --</option>
        <?php foreach ($roles as $r): ?>
          <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['label']) ?></option>
        <?php endforeach; ?>
      </select>

      <div id="permisosContainer" class="bg-white border rounded p-3">
        <em class="text-muted">Selecciona un rol para ver sus permisos.</em>
      </div>
    </div>
  </div>
</div>

<script>
const usuarioSelect = document.getElementById('usuarioSelect');
const rolSelect = document.getElementById('rolSelect');
const rolesContainer = document.getElementById('rolesContainer');
const permisosContainer = document.getElementById('permisosContainer');

usuarioSelect.addEventListener('change', async () => {
  const userId = usuarioSelect.value;
  rolesContainer.innerHTML = '<div class="text-center py-3">Cargando...</div>';
  if (!userId) return rolesContainer.innerHTML = '<em class="text-muted">Selecciona un usuario.</em>';

  const res = await fetch(`ajax_roles_usuario.php?user_id=${userId}`);
  const html = await res.text();
  rolesContainer.innerHTML = html;
});

rolSelect.addEventListener('change', async () => {
  const roleId = rolSelect.value;
  permisosContainer.innerHTML = '<div class="text-center py-3">Cargando...</div>';
  if (!roleId) return permisosContainer.innerHTML = '<em class="text-muted">Selecciona un rol.</em>';

  const res = await fetch(`ajax_permisos_rol.php?role_id=${roleId}`);
  const html = await res.text();
  permisosContainer.innerHTML = html;
});

// 🔹 Guardar roles o permisos vía AJAX + SweetAlert
document.addEventListener('submit', async (e) => {
  if (e.target.matches('.ajax-form')) {
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    const url = form.action;

    try {
      const res = await fetch(url, { method: 'POST', body: data });
      const json = await res.json();

      Swal.fire({
        icon: json.success ? 'success' : 'error',
        title: json.message || (json.success ? 'Cambios guardados' : 'Error'),
        timer: 2000,
        showConfirmButton: false
      });
    } catch (err) {
      Swal.fire({ icon: 'error', title: 'Error de conexión', text: err.message });
    }
  }
});
</script>

<?php require_once path_join(APP_ROOT, 'includes', 'footer.php'); ?>
