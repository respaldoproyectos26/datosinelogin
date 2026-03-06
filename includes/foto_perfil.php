<?php
// Valores por defecto si vienes de "Nuevo"
$foto = isset($foto) && $foto ? $foto : 'no-foto.svg';

// Helpers de escape
$h = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
$def = fn($v) => (isset($v) && $v !== '') ? $h($v) : '—';

// Fuente de la foto (si ya viene ruta tipo "2026/02/xxx.jpg" o "no-foto.svg")
$fotoSrc = "../uploads/" . ltrim($foto, "/");
?>
<div class="card perfil-card mb-3">
  <div class="card-body">
    <div class="row g-1 align-items-center">

      <!-- Foto PERFIL (única) -->
      <div class="col-12 col-md-auto">
        <div class="perfil-foto-wrap d-flex align-items-center justify-content-center">
          <img
            src="<?= $h($fotoSrc) ?>"
            alt="Foto"
            class="perfil-foto"
            id="perfilFotoPreview"
            loading="lazy"
          >
        </div>
      </div>

      <!-- Resumen -->
      <div class="col">
        <div class="row g-2">
          <div class="col-12 col-md-6">
            <div class="dato"><span class="label">Nombre:</span> <strong id="sum-nombre"><?= $def($sumNombre ?? '') ?></strong></div>
            <div class="dato"><span class="label">Fecha de Nacimiento:</span> <strong id="sum-fecha"><?= $def($sumFecha ?? '') ?></strong></div>
            <div class="dato"><span class="label">Sexo:</span> <strong id="sum-sexo"><?= $def($sumSexo ?? '') ?></strong></div>
            <div class="dato"><span class="label">Clave Elector:</span> <strong id="sum-clave"><?= $def($sumClave ?? '') ?></strong></div>
          </div>
          <div class="col-12 col-md-6">
            <div class="dato"><span class="label">Estado:</span> <strong id="sum-estado"><?= $def($sumEstado ?? '') ?></strong></div>
            <div class="dato"><span class="label">Municipio:</span> <strong id="sum-muni"><?= $def($sumMuni ?? '') ?></strong></div>
            <div class="dato"><span class="label">Celular:</span> <strong id="sum-cel"><?= $def($sumCel ?? '') ?></strong></div>
            <div class="dato"><span class="label">Email:</span> <strong id="sum-email"><?= $def($sumEmail ?? '') ?></strong></div>
          </div>
        </div>

        <!-- Controles FOTO PERFIL -->
        <div class="mt-3 d-flex flex-wrap gap-2">
          <input class="d-none" type="file" name="foto" id="foto" accept="image/jpeg,image/png">
          <button type="button" class="btn btn-secondary btn-sm" id="btnSelectFoto">
            <i class="bi bi-image"></i> Seleccionar Foto
          </button>
          <button type="button" class="btn btn-success btn-sm" id="btnActualizarFoto">
            <i class="bi bi-upload"></i> Actualizar Foto
          </button>
        </div>

      </div>
    </div>
  </div>
</div>
