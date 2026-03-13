<?php
require_once __DIR__ . '/../helpers/bootstrap.php';

require_login();
set_cache_headers('html-nocache');

# if (!tiene_permiso('ver_listapermisos')) exit('No autorizado.');

$customStyles = [
  '<link rel="stylesheet" href="../assets/css/estilos.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">',
  '<link rel="stylesheet" href="assets/css/visorreg.css">',
];

$customScripts = [
  '<script src="'.asset('assets/js/sweetalert2-v11.26.18.js').'"></script>',
  '<script src="'.asset('assets/js/dataTables.buttons.min.js').'"></script>',
  '<script src="'.asset('assets/js/jquery.dataTables-1.13.4.min.js').'"></script>',
  '<script src="'.asset('assets/js/dataTables.responsive.min.js').'"></script>',
  '<script src="'.asset('assets/js/buttons.html5.min.js').'"></script>',
  '<script src="'.asset('assets/js/buttons.print.min.js').'"></script>',
  '<script src="'.asset('assets/js/dataTables.fixedHeader.min.js').'"></script>',
  '<script src="assets/js/script.js"></script>',
];

$pageTitle = 'Lista de permisos por usuario';
$pageHeader = 'Permisos por Usuario';

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');
?>

<div class="my-2 d-flex mx-auto justify-content-center align-content-center">
  <button id="toggleFilters" class="btn btn-success">Mostrar/ocultar filtros</button>
</div>

<div class="my-2 d-flex mx-auto justify-content-center align-content-center">
  <a class="btn btn-secondary" href="../insertar_permiso.php">Agregar permiso</a>
</div>

<div class="filters-bar" id="filtersBar">
  <div class="filters-grid" id="filtersContainer">
    <!-- inputs por columna se insertan dinámicamente -->
  </div>
</div>

<div class="table-responsive">
  <table id="tablaserverside" class="table table-bordered table-striped bg-white text-dark w-100">
    <thead class="thead-dark fondohead">
      <tr>
        <th class="text-center">#</th>
        <th class="text-center">Usuario</th>
        <th class="text-center"><span class="linea">No.</span><span class="linea">Rol</span></th>
        <th class="text-center"><span class="linea">Nombre</span><span class="linea">Rol</span></th>
        <th class="text-center"><span class="linea">Descripción</span><span class="linea">Rol</span> </th>
        <th class="text-center"><span class="linea">ID</span><span class="linea">permiso</span></th>
        <th class="text-center" class="text-center">Permiso</th>
        <th class="text-center">Descripción permiso</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<div class="py-2"><hr></div>

<?php
require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php');
?>