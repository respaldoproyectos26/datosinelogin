<?php
require_once __DIR__.'/../helpers/bootstrap.php';
require_login();
csrf_check();
set_cache_headers('html-nocache');

// Opcionales por vista:
$pageTitle = 'Módulo X';
$pageHeader = 'Título';
$customStyles = [];
$customScripts = [];

// Helpers del módulo:
# require_once __DIR__.'/../helpers/helpers_exams.php';
# require_once __DIR__.'/../helpers/helpers_media.php';

require_once "../includes/headerfiltrodatos.php";
?>
<div class="container py-4">
   <!-- contenido -->
</div>
<?php require_once "../includes/footerfiltrodatos.php"; ?>
