<?php
require_once '../helpers/errores.php';
require_once '../helpers/db.php';
require_once '../helpers/auth.php';
require_once '../helpers/url.php';

require_login(); // esto valida correctamente que esté logueado

// Evitar la caché para la respuesta de PHP (HTML generado)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado
header("Pragma: no-cache"); // Para navegadores antiguos

// Tras login correcto:
session_regenerate_id(true);

$customStyles = [
  '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">',
  '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/ag-grid-community@31.1.1/styles/ag-theme-balham.css">',
];

$customScripts = [
  '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>',
  '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>',
  '<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>',
  '<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>',
  '<script src="js/script.js"></script>',
];

// 👉 Aquí defines el título y encabezado dinámico
$pageTitle = 'Filtros Datos Representante Distrital Local';
$pageHeader = 'Datos Representante Distrital Local';

require_once '../includes/headerfiltrodatos.php';
?>
     
        <div class="row text-center justify-content-center align-items-center">
            <div class="col-4">        
                <label for="filtro_nombre">Nombre(s):</label>
                <select class="form-select" id="filtro_nombre">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="col-4">
                <label for="filtro_paterno">Paterno:</label>
                <select class="form-select" id="filtro_paterno">
                    <option value="">Todos</option>
                </select>
                </div>

            <div class="col-4">
                <label for="filtro_materno">Materno:</label>
                <select class="form-select" id="filtro_materno">
                    <option value="">Todos</option>
                </select>
            </div>

            <!-- <div class="col-4">
                <label for="filtro_estado">Estado:</label>
                <select class="form-select" id="filtro_estado">
                    <option value="">Todos</option>
                </select>
            </div> -->

            <div class="col-4">
                <label for="filtro_municalc">Municipio:</label>
                <select class="form-select" id="filtro_municalc">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="col-4">
                <label for="filtro_seccion">Secci&oacute;n:</label>
                <select class="form-select" id="filtro_seccion">
                    <option value="">Todas</option>
                </select>
            </div>
        </div>

        <hr>

        <div id="loader" class="text-center my-2" style="display:none;">Cargando…</div>

        <div class="justify-content-center align-items-center">
            <table id="productsTable" class="table-bordered table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estado</th>
                        <th>Tipo cargo</th>
                        <th>Nombre</th>
                        <th>Clave elector</th>
                        <th>Celular</th>
                        <th>Secci&oacute;n</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

<!-- Botón en toolbar (DataTables lo insertará) -->

<?php
require_once '../includes/footerfiltrodatos.php';
?>