<?php
require_once __DIR__ . '/../helpers/bootstrap.php';

require_login();
set_cache_headers('html-nocache');

################################################################################################
$customStyles = [
    '<link rel="stylesheet" href="'.asset('assets/css/dataTables.bootstrap5-1.13.8.min.css').'">',
    '<link rel="stylesheet" href="'.asset('assets/css/ag-theme-balham@31.1.1.css').'">',
    '<link rel="stylesheet" href="'.asset('assets/css/buttons.bootstrap5-2.4.2.min.css').'">',
];

$customScripts = [
    '<script src="'.asset('assets/js/sweetalert2-v11.26.18.js').'"></script>',
    '<script src="'.asset('assets/js/dataTables.buttons-2.4.2.min.js').'"></script>',
    '<script src="'.asset('assets/js/buttons.bootstrap5-2.4.2.min.js').'"></script>',
    '<script src="'.asset('filtEstatal/js/script.js').'"></script>',
];
################################################################################################

// 👉 Aquí defines el título y encabezado dinámico
$pageTitle = 'Filtros Datos Representante Estatal';
$pageHeader = 'Datos Representante Estatal';

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');
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

            <div class="col-4">
                <label for="filtro_estado">Estado:</label>
                <select class="form-select" id="filtro_estado">
                    <option value="">Todos</option>
                </select>
            </div>

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

        <!-- Contenedor principal con flexbox centrado -->
        <div class="d-flex justify-content-center align-items-center my-4">
            <!-- Hacemos la tabla responsiva -->
            <div class="table-responsive">
                <table id="productsTable" class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Paterno</th>
                            <th>Materno</th>
                            <th>Estado</th>
                            <th>Municipio/Alcaldía</th>
                            <th>Sección</th>
                            <th>Foto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Aquí irán tus filas dinámicamente -->
                    </tbody>
                </table>
            </div>
        </div>

<!-- Botón en toolbar (DataTables lo insertará) -->

<?php require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php'); ?>