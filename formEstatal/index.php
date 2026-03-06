<?php
require_once __DIR__ . '/../helpers/bootstrap.php';

require_login();
set_cache_headers('html-nocache');

// (opcional) permisos
#require_permission('ver_formEstatal');

// Página: Nuevo registro (solo formulario)
$pageTitle  = 'Nuevo registro Representante Estatal';
$pageHeader = 'Registro Representante Estatal';

################################################################################################
$customStyles = [
  '<link rel="stylesheet" href="'.pathUrl('css').'/estilos.css?v='.
    (file_exists(path_join(ASSETS_DIR,'css','estilos.css')) 
      ? filemtime(path_join(ASSETS_DIR,'css','estilos.css')) 
      : time()
    ).'">',
  '<link rel="stylesheet" href="'.asset('assets/css/tom-select@2.3.1.css').'">',
  '<link rel="stylesheet" href="'.asset('assets/css/dataTables.bootstrap5-1.13.8.min.css').'">',
];
################################################################################################
$customScripts = [];
// SweetAlert
$sweetPath = ASSETS_DIR . '/js/sweetalert2-v11.26.18.js';
$sweetVersion = file_exists($sweetPath) ? filemtime($sweetPath) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/sweetalert2-v11.26.18.js?v='.$sweetVersion.'"></script>';
// Script Estatal
$scriptPath = FORM_ESTATAL_DIR . '/js/script.js';
$scriptVersion = file_exists($scriptPath) ? filemtime($scriptPath) : time();
$customScripts[] =
  '<script src="'.FORM_ESTATAL_URL.'/js/script.js?v='.$scriptVersion.'" defer></script>';
// Tom-select
$tomsPath = ASSETS_DIR . '/js/tom-select@2.3.1.min.js';
$tomsVersion = file_exists($tomsPath) ? filemtime($tomsPath) : time();
$customScripts[] =
  '<script src="'.pathUrl('js').'/tom-select@2.3.1.min.js?v='.$tomsVersion.'"></script>';
################################################################################################

$FORM_FLAGS = [
  // Distrito
  'show_dtto_fed'         => false,
  'show_dtto_loc'         => false,

  // Nombramiento
  'show_municipio_form'   => true,   // si en estatal capturan municipio del nombramiento
  'show_tipo_nombramiento'=> false,   // si aplica tipo_folio
  'tipo_propietario'      => true,
  'tipo_suplente'         => false,

  // Ruta / secciones
  'show_num_ruta'         => false,
  'show_secciones_ruta'   => false,
  'show_seccion_rc'       => false,
];

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

require_once path_join(APP_ROOT, 'includes', 'headerfiltrodatos.php');
?>

<?php
$file = path_join(FILTRO_ESTATAL_DIR,'index.php');
$version = file_exists($file) ? filemtime($file) : time();
?>
<div class="d-flex align-items-center w-100 justify-content-center mb-3">
  <a href="../filtEstatal/" class="btn btn-warning fw-bolder">Cancelar</a>
  <!--<a href="../filtEstatal/?= <?=  $version ?>" class="btn btn-warning fw-bolder">Cancelar</a>-->
</div>

<!-- ✅ CAMBIO: action apunta al endpoint real -->
<form id="formCrear" method="POST" enctype="multipart/form-data" action="save_registrofig.php" novalidate>
  <?php csrf_input(); ?>
  <hr>
  <input type="hidden" name="id" id="registro_id" value="<?php echo htmlspecialchars($id); ?>">

  <?php include path_join(APP_ROOT, 'includes', 'tabs.php'); ?>

  <!-- Sección: Figura -->
  <div class="section-title d-none">Estado / Figura</div>
  <div class="row g-3 align-items-end justify-content-center">
  
	  <div class="col-md-4 d-none">
	    <label class="form-label">Figura *</label>
	    <select name="figura" id="figura" class="form-select" required>
	      <option value="estatal" selected>Estatal</option>
	      <option value="municipal" class="d-none">Municipal</option>
	      <option value="distfed" class="d-none">Distrital Federal</option>
	      <option value="distloc" class="d-none">Distrital Local</option>
	      <option value="RC" class="d-none">RC</option>
	      <option value="RG" class="d-none">RG</option>
	      <option value="PROMOVIDO" class="d-none">PROMOVIDO</option>
	    </select>
	  </div>

    <div class="col-md-4 d-none">
      <label class="form-label">Tipo Registro</label>
      <select name="tiporeg" id="tiporeg" class="form-select">
        <option value="NORMAL" selected>Normal</option>
        <option value="RED">Red</option>
      </select>
    </div>
  </div>

  <div class="text-center my-4">
    <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm">
      <i class="bi bi-check-circle"></i> <?php echo ($id > 0) ? 'Actualizar' : 'Registrar'; ?>
    </button>
  </div>

</form>

<hr>

<?php require_once path_join(APP_ROOT, 'includes', 'footerfiltrodatos.php'); ?>