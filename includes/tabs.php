<?php
function include_here(string $file): void {
  $abs = (defined('INCLUDES_DIR') ? INCLUDES_DIR : __DIR__) . DIRECTORY_SEPARATOR . $file;
  if (is_readable($abs)) { include $abs; }
  else {
    echo '<div class="alert alert-danger small my-2">'
       . 'No se encuentra <code>'.htmlspecialchars($file).'</code><br>'
       . 'Probé: <code>'.htmlspecialchars($abs).'</code></div>';
  }
}

// ===== Flags por formulario (las define el index.php) =====
$FORM_FLAGS = $FORM_FLAGS ?? []; // ej: ['tipo_nombramiento' => true]

function show_field(string $key, bool $default=false): bool {
  global $FORM_FLAGS;
  return array_key_exists($key, $FORM_FLAGS) ? (bool)$FORM_FLAGS[$key] : $default;
}
?>

<?php include 'foto_perfil.php'; ?>
<?php include 'foto_ine.php'; ?>

<ul class="nav nav-tabs mx-auto justify-content-center mb-2" id="myTab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" data-tab="datos" type="button" role="tab">Datos Generales</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-tab="domicilio" type="button" role="tab">Domicilio</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-tab="contacto" type="button" role="tab">Contacto</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-tab="representacion" type="button" role="tab">Nombramiento</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" data-tab="proponente" type="button" role="tab">Proponente</button>
  </li>
</ul>

<!-- Contenedor propio, sin clases de BS que oculten/animen -->
<div id="tabPanels" class="border-start border-end border-top border-bottom bg-transparent">
  <section class="tab-panel" data-tab="datos">
    <?php include 'tab_datos_generales.php'; ?>
  </section>

  <section class="tab-panel" data-tab="domicilio" hidden>
    <?php include 'tab_domicilio.php'; ?>
  </section>

  <section class="tab-panel" data-tab="contacto" hidden>
    <?php include 'tab_contacto.php'; ?>
  </section>

  <section class="tab-panel" data-tab="representacion" hidden>
    <?php include 'tab_representacion.php'; ?>
  </section>

  <section class="tab-panel" data-tab="proponente" hidden>
    <?php include 'tab_proponente.php'; ?>
  </section>
</div>