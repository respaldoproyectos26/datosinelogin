<!-- Datos Inmueble -->
  <div class="section-title m-3 text-white text-center text-decoration-underline m-2 p-2"><h3>Datos Inmueble</h3></div>

  <div class="row g-1 mx-auto justify-content-center p-2 m-2">
      <div class="col-12 col-md-6 text-center m-1">
        <label class="form-label">Dirección *</label>
        <select name="Direccion" id="Direccion" class="form-select text-center" required>
          <option value="" selected>Selecciona la Dirección...</option>
          <option value="Tesoreria" >TESORERIA</option>
          <option value="DIF">DIF</option>
          <option value="OPDAPAS">OPDAPAS</option>
          <option value="D.E. LICENCIAS">D.E. LICENCIAS</option>
          <option value="D.E. AGROPECUARIO">D.E. AGROPECUARIO</option>
          <option value="D.E. EMPLEO">D.E. EMPLEO</option>
          <option value="D.E. REG. COMERCIAL">D.E. REG. COMERCIAL</option>
        </select>
      </div>
      <div class="col-12 col-md-1 text-center m-1">
        <label class="form-label">Consecutivo</label>
        <input type="text" name="Consecutivo" id="Consecutivo" class="form-control text-uppercase text-center">
      </div>
      <div class="col-12 col-md-3 text-center m-1">
        <label class="form-label">Clave Catastral *</label>
        <input type="text" name="ClaveCatastral" id="ClaveCatastral" class="form-control text-uppercase text-center" required>
      </div>
      <div class="col-12 col-md-6 text-center m-1">
        <label class="form-label">Domicilio *</label>
        <input type="text" name="domicilio" id="domicilio" class="form-control text-uppercase bg-secondary text-white text-center" readonly>
      </div>
      <div class="col-12 col-md-5 text-center m-1">
        <label class="form-label">Nombre completo *</label>
        <input type="text" name="nombrecompleto" id="nombrecompleto" class="form-control text-uppercase bg-secondary text-white text-center" readonly>
      </div>
      <div class="col-12 col-md-3 text-center m-1">
        <label class="form-label">RFC *</label>
        <input type="text" name="rfc" id="rfc" class="form-control text-uppercase text-center" required>
      </div>
      <div class="col-12 col-md-2 text-center m-1">
        <label class="form-label">Estatus *</label>
        <select name="status" id="status" class="form-select text-center" required>
          <option value="">Selecciona el estatus...</option>
          <option value="ACTIVO" selected>ACTIVO</option>
          <option value="BAJA">BAJA</option>
        </select>
      </div>
      <div class="col-12 col-md-2 text-center m-1">
        <label class="form-label">Valor Catastral *</label>
        <div class="input-group">
          <span class="input-group-text">$</span>
          <input type="text" name="ValorCatastral" id="ValorCatastral" class="form-control text-center" required>
        </div>
      </div>
      <div class="col-12 col-md-2 text-center m-1">
        <label class="form-label">Superficie Terreno *</label>
        <div class="input-group">
          <input type="text" name="supterreno" id="supterreno" class="form-control text-center" required>
          <span class="input-group-text">mts<sup>2</sup></span>
        </div>
      </div>
      <div class="col-12 col-md-2 text-center m-1">
        <label class="form-label">Superficie Construcción *</label>
        <div class="input-group">
          <input type="text" name="supconstruccion" id="supconstruccion" class="form-control text-center" required>
          <span class="input-group-text">mts<sup>2</sup></span>
        </div>
        
      </div>
      <div class="col-12 col-md-12 text-center m-1">
        <label class="form-label">Descripción</label>
        <input type="text" name="descripcion" id="descripcion" class="form-control text-uppercase text-center">
      </div>
  </div>
