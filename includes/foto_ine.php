<?php
// Placeholder INE (ajusta ruta si ya tienes una imagen)
$inePlaceholder = asset('assets/img/ine-placeholder.jpg');
?>
<div class="container my-3">
  <div class="row g-3 justify-content-center align-items-center">

    <div class="col-12 col-md-3">
      <label for="copia_credelec" class="form-label text-white">¿Trae copia INE?</label>
      <select name="copia_credelec" id="copia_credelec" class="form-select" required>
        <option value="SI">SI</option>
        <option value="NO">NO</option>
      </select>
    </div>

    <div id="copiaIneBlock" class="col-12 col-md-8 d-none align-items-center gap-3">
      <div class="text-center">
        <img id="ineFotoPreview"
        src =""
            src="<?= htmlspecialchars($inePlaceholder) ?>"
            data-default-src="<?= htmlspecialchars($inePlaceholder) ?>"
            alt="INE"
            style="width:160px;height:110px;object-fit:cover;border-radius:10px;border:1px solid rgba(255,255,255,.35);">
      </div>

      <div class="flex-grow-1">
        <input type="file" id="foto_ine" name="foto_ine" accept="image/jpeg,image/png" class="d-none">
        <button type="button" id="btnSelectFotoIne" class="btn btn-outline-light">
          <i class="bi bi-card-image"></i> Seleccionar INE
        </button>
        <small class="d-block text-white-50 mt-2">JPG/PNG, máx 3MB</small>
      </div>
    </div>

  </div>
</div>
