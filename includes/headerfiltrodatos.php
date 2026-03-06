<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
    <meta name="csrf" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES) ?>">
    <title><?php echo $pageTitle ?? 'Mi Sitio'; ?></title>
    <link rel="icon" href="<?= asset('assets/img/share-ios-icon.svg') ?>">

    <link href="<?= asset('assets/css/bootstrap@5.3.3.min.css') ?>" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="<?= pathUrl('css') ?>/all.min.css?v=<?= filemtime(path_join(ASSETS_DIR,'css','all.min.css')) ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= pathUrl('css') ?>/bootstrap-icons/bootstrap-icons.css?v=<?= filemtime(path_join(ASSETS_DIR,'css','bootstrap-icons','bootstrap-icons.css')) ?>">

    <!-- Estilos globales -->
    <link rel="stylesheet" href="<?= asset('assets/css/estilos.css') ?>?v=1.0">

    <?php if (!empty($customStyles)) echo implode("\n", $customStyles); ?>

    <script src="<?= JS_URL ?>/csrf.js?v=<?= filemtime(path_join(ASSETS_DIR,'js','csrf.js')) ?>" defer></script>
</head>

<body class="d-flex flex-column min-vh-100">
  <video autoplay muted loop id="backgroundVideo">
    <source src="<?= asset('assets/video/fondo.mp4') ?>" type="video/mp4">
  </video>

  <!-- Botón hamburguesa arriba -->
  <nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
      <!-- Botón escritorio: colapsa sidebar -->
      <button class="navbar-toggler d-none d-md-inline-block" id="btnCollapseSidebar" type="button">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Botón móvil: abre offcanvas -->
      <button class="navbar-toggler d-md-none" id="botonhamburguer" type="button"
              data-bs-toggle="offcanvas" data-bs-target="#offcanvasMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <span class="navbar-text text-white ms-2"><?= $pageHeader ?? 'Panel' ?></span>
      <?php $u = $_SESSION['user']['usuario'] ?? null; ?>
      <span class="navbar-text text-white ms-2"><?= $u ? mb_strtoupper($u) : 'INVITADO' ?></span>
    </div>
  </nav>

  <?php include_once INCLUDES_DIR . '/navfiltrodatos.php'; ?>

  <div class="main-container">
    <h2><?= $pageHeader ?? '' ?></h2>
    <hr>
