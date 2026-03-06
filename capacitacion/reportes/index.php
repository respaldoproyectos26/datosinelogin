<?php
ob_start(); // start output buffering
require_once "../../helpers/errores.php";
require_once "../../helpers/auth.php";
require_once "../../helpers/cache_headers.php";
require_once "../../helpers/auth.php";
require_once "../../helpers/url.php";
require_once "../../helpers/csrf.php";
require_once "../../helpers/flash.php";
require_login(); csrf_check(); set_cache_headers('html-nocache'); render_flash_swals();
ob_end_flush(); // send all output

require_login();

if (!auth()->can('ver_reportes')) {
    denegar_acceso();
}

$extraCSS = [
  'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css',
  'https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css',
  '../assets/reportes.css'
];
$extraJS = array_merge($extraJS ?? [], [
  'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',
  'https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js',
  'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js',
  'https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js',
  'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js',
  'https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js',
  'https://cdn.jsdelivr.net/npm/chart.js',          // <= NUEVO
  '../assets/reportes.js'
]);


?>

<?php include_once '../../includes/header_base.php'; ?>

<div class="content container">

        <!-- Encabezado -->
        <h2 class="text-center text-white bg-primary py-2 rounded">
            📊 Reportes de Asistencia / Capacitación
        </h2>

        <!-- Filtros -->
        <div class="row my-3">
            <div class="col-md-4">
                <input type="text" id="filtroCurso" class="form-control" placeholder="Filtrar por curso">
            </div>
            <div class="col-md-4">
                <input type="text" id="filtroNombre" class="form-control" placeholder="Filtrar por nombre">
            </div>
            <div class="col-md-4">
                <button id="btnBuscar" class="btn btn-success w-100">🔍 Buscar</button>
            </div>
        </div>

        <!-- Tabla de reporte -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Curso</th>
                        <th>Fecha</th>
                        <th>Asistencia</th>
                    </tr>
                </thead>
                <tbody id="tablaReporte">
                    <!-- Datos llenados por JS -->
                </tbody>
            </table>
        </div>

        <!-- Botones de acción -->
        <div class="d-flex justify-content-end mt-3">
            <button id="btnExportarExcel" class="btn btn-primary me-2">📥 Exportar Excel</button>
            <button id="btnImprimir" class="btn btn-secondary">🖨️ Imprimir</button>
        </div>

    </div>

    <!-- Navbar de ejemplo con dropdown -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mt-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Menú</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Opciones
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Perfil</a></li>
                            <li><a class="dropdown-item" href="#">Configuración</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#">Cerrar sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <?php include_once '../../includes/footer_base.php'; ?>
