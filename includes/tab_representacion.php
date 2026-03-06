<!-- Representación -->
<div class="section-title m-3 text-white text-center text-decoration-underline m-2">
  <h3>Nombramiento</h3>
</div>

<div class="row g-3 justify-content-center">

  <!-- Estatus (oculto pero viaja) -->
  <div class="col-md-4 d-none">
    <label class="form-label">Estatus</label>
    <select name="estatus" id="estatus" class="form-select">
      <option value="ACTIVO" selected>ACTIVO</option>
      <option value="BAJA">BAJA</option>
    </select>
  </div>

  <!-- Estado formato (siempre SAN LUIS POTOSI) -->
  <div class="col-md-4">
    <label class="form-label">Estado formato</label>
    <input type="text" name="edo_form" class="form-control text-uppercase"
           value="SAN LUIS POTOSI" readonly>
  </div>

  <!-- Distrito (Federal o Local) -->
  <?php if (show_field('show_dtto_fed')): ?>
    <div class="col-md-2">
      <label class="form-label">Dtto. Federal</label>
      <input type="text" name="distfed_form" id="distfed_form" class="form-control">
    </div>
  <?php endif; ?>

  <?php if (show_field('show_dtto_loc')): ?>
    <div class="col-md-2">
      <label class="form-label">Dtto. Local</label>
      <!-- OJO: conservamos name="distfed_form" para no tocar backend -->
      <input type="text" name="distfed_form" id="distloc_form" class="form-control">
    </div>
  <?php endif; ?>

  <!-- Municipio -->
  <?php if (show_field('show_municipio_form')): ?>
    <div class="col-md-2">
      <label class="form-label">Municipio</label>
      <input type="text" name="munic_form" id="munic_form" class="form-control">
    </div>
  <?php endif; ?>

  <!-- Tipo Nombramiento (un solo select) -->
  <?php if (show_field('show_tipo_nombramiento')): ?>
    <div class="col-md-5">
      <label class="form-label">Tipo Nombramiento</label>
      <select name="_tipo_form_ui" id="tipo_form_ui" class="form-select">
        <option value="">Selecciona un tipo</option>

        <?php if (show_field('tipo_propietario')): ?>
          <option value="PROPIETARIO 1">PROPIETARIO 1</option>
          <option value="PROPIETARIO 2">PROPIETARIO 2</option>
        <?php endif; ?>

        <?php if (show_field('tipo_suplente')): ?>
          <option value="SUPLENTE 1">SUPLENTE 1</option>
          <option value="SUPLENTE 2">SUPLENTE 2</option>
        <?php endif; ?>
      </select>
    </div>
  <?php endif; ?>

  <!-- No. Ruta -->
  <?php if (show_field('show_num_ruta')): ?>
    <div class="col-md-5">
      <label class="form-label">No. Ruta</label>
      <select name="_num_ruta_ui" id="num_ruta_ui" class="form-select">
        <option value="">Selecciona una ruta</option>
      </select>
    </div>
  <?php endif; ?>

  <!-- Secciones de ruta (texto) -->
  <?php if (show_field('show_secciones_ruta')): ?>
    <div class="col-md-5">
      <label class="form-label">Secciones de la ruta</label>
      <input type="text" name="secciones_ruta" id="secciones_ruta" value="" class="form-control">
    </div>

  <!-- RC: input "Sección" (reusa el mismo name="secciones_ruta" para no tocar backend) -->
  <?php elseif (show_field('show_seccion_rc')): ?>
    <div class="col-md-5">
      <label class="form-label">Sección</label>
      <input type="text" name="secciones_ruta" id="secciones_ruta" value="" class="form-control">
    </div>
  <?php endif; ?>

  <!-- ✅ HIDDENS REALES (SIEMPRE, 1 SOLA VEZ) -->
  <input type="hidden" name="tipo_form" id="tipo_form" value="">
  <input type="hidden" name="num_ruta"  id="num_ruta"  value="">

</div>

<hr>
