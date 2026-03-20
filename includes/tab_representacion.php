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
    <div class="col-md-4">
      <label class="form-label" for="munic_form">Municipio:</label>
      <select class="form-select" id="munic_form" name="munic_form">
        <option value="">Seleccione</option>
        <option value="AHUALULCO">AHUALULCO</option>
        <option value="ALAQUINES">ALAQUINES</option>
        <option value="AQUISMON">AQUISMON</option>
        <option value="ARMADILLO DE LOS INFANTE">ARMADILLO DE LOS INFANTE</option>
        <option value="CARDENAS">CARDENAS</option>
        <option value="CATORCE">CATORCE</option>
        <option value="CEDRAL">CEDRAL</option>
        <option value="CERRITOS">CERRITOS</option>
        <option value="CERRO DE SAN PEDRO">CERRO DE SAN PEDRO</option>
        <option value="CIUDAD DEL MAIZ">CIUDAD DEL MAIZ</option>
        <option value="CIUDAD FERNANDEZ">CIUDAD FERNANDEZ</option>
        <option value="TANCANHUITZ">TANCANHUITZ</option>
        <option value="CIUDAD VALLES">CIUDAD VALLES</option>
        <option value="CIUDAD VALLES">CIUDAD VALLES</option>
        <option value="CHARCAS">CHARCAS</option>
        <option value="EBANO">EBANO</option>
        <option value="GUADALCAZAR">GUADALCAZAR</option>
        <option value="HUEHUETLAN">HUEHUETLAN</option>
        <option value="LAGUNILLAS">LAGUNILLAS</option>
        <option value="MATEHUALA">MATEHUALA</option>
        <option value="MEXQUITIC DE CARMONA">MEXQUITIC DE CARMONA</option>
        <option value="MOCTEZUMA">MOCTEZUMA</option>
        <option value="RAYON">RAYON</option>
        <option value="RIOVERDE">RIOVERDE</option>
        <option value="SALINAS">SALINAS</option>
        <option value="SAN ANTONIO">SAN ANTONIO</option>
        <option value="SAN CIRO DE ACOSTA">SAN CIRO DE ACOSTA</option>
        <option value="SAN LUIS POTOSI">SAN LUIS POTOSI</option>
        <option value="SAN MARTIN CHALCHICUAUTLA">SAN MARTIN CHALCHICUAUTLA</option>
        <option value="SAN NICOLAS TOLENTINO">SAN NICOLAS TOLENTINO</option>
        <option value="SAN VICENTE TANCUAYALAB">SAN VICENTE TANCUAYALAB</option>
        <option value="SANTA CATARINA">SANTA CATARINA</option>
        <option value="SANTA MARIA DEL RIO">SANTA MARIA DEL RIO</option>
        <option value="SANTO DOMINGO">SANTO DOMINGO</option>
        <option value="SOLEDAD DE GRACIANO SANCHEZ">SOLEDAD DE GRACIANO SANCHEZ</option>
        <option value="TAMASOPO">TAMASOPO</option>
        <option value="TAMAZUNCHALE">TAMAZUNCHALE</option>
        <option value="TAMPACAN">TAMPACAN</option>
        <option value="TAMPAMOLON CORONA">TAMPAMOLON CORONA</option>
        <option value="TAMUIN">TAMUIN</option>
        <option value="TANLAJAS">TANLAJAS</option>
        <option value="TANQUIAN DE ESCOBEDO">TANQUIAN DE ESCOBEDO</option>
        <option value="TIERRA NUEVA">TIERRA NUEVA</option>
        <option value="VANEGAS">VANEGAS</option>
        <option value="VENADO">VENADO</option>
        <option value="VILLA DE ARISTA">VILLA DE ARISTA</option>
        <option value="VILLA DE ARRIAGA">VILLA DE ARRIAGA</option>
        <option value="VILLA DE GUADALUPE">VILLA DE GUADALUPE</option>
        <option value="VILLA DE LA PAZ">VILLA DE LA PAZ</option>
        <option value="VILLA DE RAMOS">VILLA DE RAMOS</option>
        <option value="VILLA DE REYES">VILLA DE REYES</option>
        <option value="VILLA HIDALGO">VILLA HIDALGO</option>
        <option value="VILLA JUAREZ">VILLA JUAREZ</option>
        <option value="AXTLA DE TERRAZAS">AXTLA DE TERRAZAS</option>
        <option value="XILITLA">XILITLA</option>
        <option value="ZARAGOZA">ZARAGOZA</option>
        <option value="EL NARANJO">EL NARANJO</option>
        <option value="MATLAPA">MATLAPA</option>
      </select>
    </div>    
    <!-- <div class="col-md-2">
      <label class="form-label">Municipio</label>
      <input type="text" name="munic_form" id="munic_form" class="form-control">
    </div> -->
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
