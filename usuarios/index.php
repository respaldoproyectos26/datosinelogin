<?php
require_once __DIR__ . '/../helpers/bootstrap.php';

require_login();
set_cache_headers('html-nocache');

// require_permission('ver_usuarios');

$pageTitle  = 'Usuarios';
$pageHeader = 'Gestión de Usuarios';

$customStyles = [
  '<link rel="stylesheet" href="'.asset('assets/css/jquery.dataTables.min.css').'">',
  '<link rel="stylesheet" href="'.asset('assets/css/responsive.dataTables.min.css').'">',
  '<link rel="stylesheet" href="assets/css/visorreg.css">',
];

$customScripts = [
  '<script src="'.asset('assets/js/sweetalert2-v11.26.18.js').'"></script>',
  '<script src="'.asset('assets/js/jquery.dataTables-1.13.4.min.js').'"></script>',
  '<script src="'.asset('assets/js/dataTables.responsive.min.js').'"></script>',
  '<script src="assets/js/usuarios.js"></script>',
];

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');
?>

<div class="container py-3">
  <div class="d-flex gap-2 mb-3">
    <button class="btn btn-success" id="btnNuevo">Nuevo usuario</button>
  </div>

  <table id="tblUsuarios" class="display nowrap" style="width:100%">
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuario</th>
        <th>Estado</th>
        <th>Creado</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" id="userForm">
      <?php csrf_input(); ?>
      <div class="modal-header">
        <h5 class="modal-title" id="userModalTitle">Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-black">
        <input type="hidden" name="id" id="u_id">

        <div class="mb-2">
          <label class="form-label">Usuario</label>
          <input type="text" class="form-control" name="usuario" id="u_usuario" required autocomplete="off">
        </div>

        <div class="mb-2">
          <label class="form-label">Estado</label>
          <input type="text" class="form-control" name="estado" id="u_estado" maxlength="25" readonly required>
        </div>

        <div class="mb-2">
          <label class="form-label">Password (requerido al crear; opcional al editar)</label>
          <input type="password" class="form-control" name="password" id="u_password" autocomplete="new-password">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-success" type="submit">Guardar</button>
      </div>
    </form>
  </div>
</div>

<?php require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php'); ?>
