<!-- Domicilio -->
<div class="section-title text-white text-center text-decoration-underline mb-3">
  <h3>Domicilio</h3>
</div>

<div class="row g-3 justify-content-center">
  <div class="col-12 col-md-4">
    <label for="calle" class="form-label">Calle *</label>
    <input type="text" name="calle" id="calle" class="form-control text-uppercase" required>
  </div>

  <div class="col-12 col-md-2">
    <label for="numext" class="form-label">No. Ext.</label>
    <input type="text" name="numext" id="numext" class="form-control text-uppercase">
  </div>

  <div class="col-12 col-md-2">
    <label for="numint" class="form-label">No. Int.</label>
    <input type="text" name="numint" id="numint" class="form-control text-uppercase">
  </div>

  <div class="col-12 col-md-2">
    <label for="cp" class="form-label">C.P. *</label>
    <!--<input type="text" name="cp" id="cp" class="form-control" maxlength="5" required>-->
    <select id="cp" name="cp" placeholder="Escribe CP..." autocomplete="off"></select>
  </div>

  <div class="col-12 col-md-4">
    <label for="colonia" class="form-label">Colonia *</label>
    <!--
    <input type="text" name="colonia" id="colonia" class="form-control text-uppercase" required>
    -->
    <select name="colonia" id="colonia" class="form-select text-uppercase" required>
      <option value="">Escribe CP para ver colonias</option>
    </select>
  </div>

  <div class="col-12 col-md-4 d-none">
    <label for="idedo" class="form-label">Estado *</label>
    <select name="idedo" id="idedo" class="form-select" required>
    </select>
  </div>

  <div class="col-12 col-md-5">
    <label for="idmunicalc" class="form-label">Municipio *</label>
    <select name="idmunicalc" id="idmunicalc" class="form-select" disabled>
      <option value="">Escribe CP para fijar municipio</option>
    </select>

    <!-- ESTE ES EL QUE SE ENVÍA -->
    <input type="hidden" name="idmunicalc_locked" id="idmunicalc_locked" value="">
  </div>

  <div class="col-12 col-md-5">
    <label for="seccion" class="form-label">Sección *</label>
    <select name="seccion" id="seccion" class="form-select" required>
      <option value="">Selecciona un municipio primero</option>
    </select>
  </div>
</div>

<hr>
