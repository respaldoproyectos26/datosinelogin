<?php
/**
 * nav.php
 * ✔ HTML idéntico al original (CSS safe)
 * ✔ Sin $PREFIX / $prefix_nav
 * ✔ Usa app_url()
 * ✔ Offcanvas (móvil) + Sidebar (desktop)
 */

function render_nav_items(bool $isSidebar = false) { ?>

<ul class="nav nav-pills flex-column">

  <!-- INICIO -->
  <li class="nav-item">
    <a class="nav-link <?= nav_active('bienvenida.php') ?>"
       href="<?= app_url('bienvenida.php') ?>">
      <i class="bi bi-house-door"></i> <span>Inicio</span>
    </a>
  </li>

  <!-- ESTRUCTURA -->
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle"
       data-bs-toggle="dropdown"
       <?= $isSidebar ? 'data-bs-display="static" aria-expanded="false"' : '' ?>
       href="#">
      <i class="bi bi-diagram-3-fill"></i> <span>Estructura</span>
    </a>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item text-white <?= nav_active('filtMunicipal/') ?>"
             href="<?= app_url('filtMunicipal/') ?>">
        <i class="bi bi-card-checklist"></i> Rep. Municipal</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('filtDistFed/') ?>"
             href="<?= app_url('filtDistFed/') ?>">
        <i class="bi bi-card-checklist"></i> Rep. Distrito Fed.</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('filtDistLoc/') ?>"
             href="<?= app_url('filtDistLoc/') ?>">
        <i class="bi bi-card-checklist"></i> Rep. Distrito Local</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('filtEstatal/') ?>"
             href="<?= app_url('filtEstatal/') ?>">
        <i class="bi bi-card-checklist"></i> Otro</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('filtrodatos/') ?>"
             href="<?= app_url('filtrodatos/') ?>">
        <i class="bi bi-card-checklist"></i> RG</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('filtrodatos2/') ?>"
             href="<?= app_url('filtrodatos2/') ?>">
        <i class="bi bi-card-checklist"></i> RC</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('filtrodatos3/') ?>"
             href="<?= app_url('filtrodatos3/') ?>">
        <i class="bi bi-card-checklist"></i> Promovidos</a></li>

      <li>
        <a class="dropdown-item text-white <?= nav_active('contadores/') ?>"
           href="<?= app_url('contadores/') ?>">
          <i class="bi bi-graph-up"></i> Reporte Estructura
        </a>
      </li>
    </ul>
  </li>

  <!-- INEs -->
  <?php if (auth()->can('ver_registros')): ?>
  <li class="nav-item dropdown d-none">
    <a class="nav-link dropdown-toggle"
       data-bs-toggle="dropdown"
       <?= $isSidebar ? 'data-bs-display="static" aria-expanded="false"' : '' ?>
       href="#">
      <i class="bi bi-menu-button"></i> <span>INEs</span>
    </a>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item text-white <?= nav_active('index.php') ?>"
             href="<?= app_url('index.php') ?>">
        <i class="bi bi-bag-check-fill"></i> Escaneo</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('tablero.php') ?>"
             href="<?= app_url('tablero.php') ?>">
        <i class="bi bi-bag-check-fill"></i> Tablero</a></li>

      <li><a class="dropdown-item text-white <?= nav_active('resumen.php') ?>"
             href="<?= app_url('resumen.php') ?>">
        <i class="bi bi-bag-check-fill"></i> Resumen</a></li>
    </ul>
  </li>
  <?php endif; ?>

  <!-- CAPACITACIÓN -->
  <?php if (can('ver_capacitacion')): ?>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle"
        data-bs-toggle="dropdown"
        data-bs-display="static"
        aria-expanded="false"
        href="#">
      <i class="bi bi-mortarboard"></i> <span>Capacitación</span>
    </a>

    <ul class="dropdown-menu">
      <li>
        <a class="dropdown-item text-white <?= nav_active('capacitacion/') ?>"
           href="<?= app_url('capacitacion/index.php') ?>">
          <i class="bi bi-grid-3x3-gap-fill"></i> Menú principal
        </a>
      </li>

      <?php if (can('ver_biblioteca')): ?>
      <li>
        <a class="dropdown-item text-white <?= nav_active('capacitacion/biblioteca/') ?>"
           href="<?= app_url('capacitacion/biblioteca/index.php') ?>">
          <i class="bi bi-collection-play"></i> Biblioteca
        </a>
      </li>

      <li>
        <a class="dropdown-item text-white"
           href="<?= app_url('capacitacion/media/index.php') ?>">
          <i class="bi bi-camera-video"></i> Medios
        </a>
      </li>
      <?php endif; ?>

      <?php if (can('ver_asistencia_capacitacion')): ?>
      <li>
        <a class="dropdown-item text-white <?= nav_active('capacitacion/asistencia/') ?>"
           href="<?= app_url('capacitacion/asistencia/index.php') ?>">
          <i class="bi bi-clock-history"></i> Asistencia
        </a>
      </li>
      <?php endif; ?>

      <?php if (can('ver_evaluaciones')): ?>
      <li>
        <a class="dropdown-item text-white <?= nav_active('evaluaciones/') ?>"
           href="<?= app_url('evaluaciones/index.php') ?>">
          <i class="bi bi-ui-checks-grid"></i> Evaluaciones
        </a>
      </li>

        <?php if (can('crear_evaluaciones')): ?>
        <li>
          <a class="dropdown-item text-white <?= nav_active('evaluaciones/crear.php') ?>"
             href="<?= app_url('evaluaciones/crear.php') ?>">
            <i class="bi bi-plus-circle"></i> Crear examen
          </a>
        </li>
        <?php endif; ?>

        <?php if (can('ver_resultados_evaluaciones')): ?>
        <li>
          <a class="dropdown-item text-white <?= nav_active('evaluaciones/resultados.php') ?>"
             href="<?= app_url('evaluaciones/resultados.php') ?>">
            <i class="bi bi-bar-chart"></i> Resultados
          </a>
        </li>
        <?php endif; ?>
      <?php endif; ?>
    </ul>
  </li>
  <?php endif; ?>

  <!-- GEOLOCALIZACIÓN -->
  <?php if (auth()->can('ver_geolocalizacion')): ?>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle"
       data-bs-toggle="dropdown"
       <?= $isSidebar ? 'data-bs-display="static" aria-expanded="false"' : '' ?>
       href="#">
      <i class="fas fa-map-marker-alt"></i> <span>Geolocalización</span>
    </a>

    <ul class="dropdown-menu">
      <li>
        <a class="dropdown-item text-white <?= nav_active('geolocalizador/mapa.php') ?>"
           href="<?= app_url('geolocalizador/mapa.php') ?>">
          <i class="fas fa-map"></i> Mapa ubicaciones
        </a>
      </li>

      <?php if (auth()->can('registrar_manual_geolocalizacion')): ?>
      <li class="d-none">
        <a class="dropdown-item text-white <?= nav_active('geolocalizador/index.php') ?>"
           href="<?= app_url('geolocalizador/index.php') ?>">
          <i class="fa fa-id-card"></i> Alta individual
        </a>
      </li>

      <li class="d-none">
        <a class="dropdown-item text-white <?= nav_active('geolocalizador/form_masivo.php') ?>"
           href="<?= app_url('geolocalizador/form_masivo.php') ?>">
          <i class="bi bi-clipboard-data"></i> Carga masiva
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </li>
  <?php endif; ?>

  <!-- LISTA PERMISOS -->
  <?php if (auth()->can('ver_lista_permisos')): ?>
  <li class="nav-item">
    <a class="nav-link <?= nav_active('visorreg/') ?>"
       href="<?= app_url('visorreg/') ?>">
      <i class="bi bi-clipboard-fill"></i> <span>Lista de Permisos</span>
    </a>
  </li>
  <?php endif; ?>

  <!-- GESTIÓN USUARIOS -->
  <?php # if (auth()->can('ver_usuarios')): ?>
  <li class="nav-item">
    <a class="nav-link <?= nav_active('usuarios/') ?>"
       href="<?= app_url('usuarios/') ?>">
      <i class="bi bi-clipboard-fill"></i> <span>Gestión de Usuarios</span>
    </a>
  </li>
  <?php # endif; ?>

  <!-- GRÁFICOS -->
  <?php if (auth()->can('ver_graficos')): ?>
  <li class="nav-item">
    <a class="nav-link <?= nav_active('graficos.php') ?>"
       href="<?= app_url('graficos.php') ?>">
      <i class="bi bi-bar-chart-line"></i> <span>Gráficos</span>
    </a>
  </li>
  <?php endif; ?>

  <!-- ROLES -->
  <?php if (auth()->can('ver_panel_roles')): ?>
  <li class="nav-item">
    <a class="nav-link <?= nav_active('gestion_roles.php') ?>"
       href="<?= app_url('gestion_roles.php') ?>">
      <i class="bi bi-people"></i> <span>Roles</span>
    </a>
  </li>
  <?php endif; ?>

  <!-- SALIR -->
  <li class="nav-item">
    <a class="nav-link text-danger"
       href="<?= app_url('logout.php') ?>">
      <i class="bi bi-box-arrow-right"></i> <span>Salir</span>
    </a>
  </li>

</ul>
<?php } ?>

<!-- OFFCANVAS (MÓVIL) -->
<div class="offcanvas offcanvas-start text-bg-dark d-md-none" id="offcanvasMenu">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title">Menú</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <?php render_nav_items(false); ?>
  </div>
</div>

<!-- SIDEBAR (ESCRITORIO) -->
<div class="sidebar d-none d-md-block text-bg-dark position-fixed h-100">
  <div class="p-3">
    <h5>Menú</h5>
    <hr class="text-white">
    <?php render_nav_items(true); ?>
  </div>
</div>
