<!-- Datos Generales -->
<div class="section-title text-white text-center text-decoration-underline mb-3">
  <h3>Datos Generales</h3>
</div>

<div class="row g-1 justify-content-center px-3 mb-3">
  <div class="col-md-3">
      <label class="form-label">Fecha Formato *</label>
      <input type="date" name="fecha_form" id="fecha_form" class="form-control" required>
  </div>
  <div class="col-md-3">
    <label class="form-label">Folio Formato *</label>
    <input type="text" name="folio_form" class="form-control text-uppercase" required>
  </div>
  <div class="col-12 col-md-3">
    <label for="paterno" class="form-label">Apellido Paterno *</label>
    <input type="text" name="paterno" id="paterno" class="form-control text-uppercase text-center" required>
  </div>
  <div class="col-12 col-md-3">
    <label for="materno" class="form-label">Apellido Materno *</label>
    <input type="text" name="materno" id="materno" class="form-control text-uppercase text-center" required>
  </div>
  <div class="col-12 col-md-6">
    <label for="nombre" class="form-label">Nombre(s) *</label>
    <input type="text" name="nombre" id="nombre" class="form-control text-uppercase text-center" required>
  </div>
</div>

<div class="row g-1 justify-content-center px-3 mb-3">
  <div class="col-12 col-md-6">
    <label class="form-label">Fecha de Nacimiento *</label>
    <div class="row g-2">
      <div class="col-4">
        <select name="dianac" id="dianac" class="form-select" required>
          <option value="">Día</option>
          <?php for($i=1;$i<=31;$i++) echo '<option value="'.sprintf('%02d',$i).'">'.$i.'</option>'; ?>
        </select>
      </div>
      <div class="col-4">
        <select name="mesnac" id="mesnac" class="form-select" required>
          <option value="">Mes</option>
          <option value="01">Enero</option><option value="02">Febrero</option>
          <option value="03">Marzo</option><option value="04">Abril</option>
          <option value="05">Mayo</option><option value="06">Junio</option>
          <option value="07">Julio</option><option value="08">Agosto</option>
          <option value="09">Septiembre</option><option value="10">Octubre</option>
          <option value="11">Noviembre</option><option value="12">Diciembre</option>
        </select>
      </div>
      <div class="col-4">
        <select name="yearnac" id="yearnac" class="form-select" required>
          <option value="">Año</option>
          <?php 
            $y=date('Y');
            for ($a = $y; $a >= 1900; $a--) {
                echo "<option>$a</option>";
            }
          ?>
        </select>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-6 d-none">
    <label for="estadodom" class="form-label">Estado Representado *</label>
    <select name="estadodom" id="estadodom" class="form-select" disabled>
      <!--<option value="">Estado</option>-->
      <?php
        $estados = [
          "SAN LUIS POTOSI",
        ];
        /*
        $estados = [
          "AGUASCALIENTES", "BAJA CALIFORNIA", "BAJA CALIFORNIA SUR", "CAMPECHE",
          "COAHUILA", "COLIMA", "CHIAPAS", "CHIHUAHUA", "CIUDAD DE MEXICO",
          "DURANGO", "GUANAJUATO", "GUERRERO", "HIDALGO", "JALISCO", "MEXICO",
          "MICHOACAN", "MORELOS", "NAYARIT", "NUEVO LEON", "OAXACA", "PUEBLA",
          "QUERETARO", "QUINTANA ROO", "SAN LUIS POTOSI", "SINALOA", "SONORA",
          "TABASCO", "TAMAULIPAS", "TLAXCALA", "VERACRUZ", "YUCATAN", "ZACATECAS"
        ];
        */
        foreach ($estados as $estado) {
          echo "<option value=\"$estado\" selected>$estado</option>";
        }
      ?>
    </select>
  </div>
</div>

<div class="row g-1 justify-content-center px-3 mb-3">
  <div class="col-12 col-md-2">
    <label for="genero" class="form-label">Sexo *</label>
    <select name="genero" id="genero" class="form-select" required>
      <option value="">Seleccione</option>
      <option value="F">Mujer</option>
      <option value="M">Hombre</option>
    </select>
  </div>

  <div class="col-12 col-md-4">
    <label for="clave_elector" class="form-label">Clave de Elector *</label>
    <input type="text" name="clave_elector" id="clave_elector" class="form-control text-center" required>
  </div>

  <div class="col-12 col-md-4">
    <label for="escolaridad" class="form-label">Escolaridad</label>
    <select name="escolaridad" id="escolaridad" class="form-select">
      <option value="">Seleccione</option>
      <option value="primaria">Educación Primaria</option>
      <option value="secundaria">Educación Secundaria</option>
      <option value="bachillerato">Bachillerato</option>
      <option value="licenciatura">Licenciatura</option>
      <option value="maestria">Maestría</option>
      <option value="doctorado">Doctorado</option>
      <option value="especialidad">Especialidad</option>
      <option value="posgrado">Posgrado</option>
      <option value="diplomado">Diplomado</option>
      <option value="certificacion">Certificación Profesional</option>
      <option value="otros">Otros</option>
    </select>
  </div>
</div>


<div class="row g-1 justify-content-center px-3 mb-3">
<!--
  <div class="col-12 col-md-2">
    <label for="copia_credelec" class="form-label">Copia Credencial Elector</label>
    <select name="copia_credelec" id="copia_credelec" class="form-select">
      <option value="SI">Sí</option>
      <option value="NO" selected>No</option>
    </select>
  </div>
-->

  <div class="col-12 col-md-6">
    <label for="afinidad" class="form-label">Afinidad</label>
    <select name="afinidad" id="afinidad" class="form-select" onchange="mostrarCampoOtro(this)">
      <option value="">Elige la afinidad</option>
      <!--
      <option value="PAN">PAN</option>
      <option value="PRI">PRI</option>
      <option value="PRD">PRD</option>
      <option value="Morena">Morena</option>
      -->
      <option value="PVEM">PVEM</option>
      <option value="PT">PT</option>
      <!--
      <option value="MC">MC</option>
      <option value="APN_AlianzaPatriótica">APN – Alianza Patriótica Nacional</option>
      <option value="APN_HumanismoMexicano">APN – Humanismo Mexicano</option>
      <option value="APN_5deMayoReformador">APN – 5 de Mayo Mov. Reformador</option>
      <option value="APN_QueSigaDemocracia">APN – Que Siga la Democracia</option>
      <option value="APN_ArcoirisMX">APN – Movimiento Arcoíris por México</option>
      <option value="APN_Frente4T">APN – Frente por la 4T</option>
      <option value="otro">Otro</option>
      -->
    </select>

    <div id="otroPartidoInput" class="mt-2" style="display:none;">
      <label for="otroPartido">Especifica el nombre del partido:</label>
      <input type="text" name="otro" id="otroPartido" class="form-control">
    </div>
  </div>
</div>

<hr>

<script>
  function mostrarCampoOtro(select) {
    const otroInput = document.getElementById("otroPartidoInput");
    const input = document.getElementById("otroPartido");
    if (select.value === "otro") {
      otroInput.style.display = "block";
    } else {
      otroInput.style.display = "none";
      input.value = "";
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const militante = document.getElementById('militante');
    const numMilit = document.getElementById('num_milit');

    if (militante && numMilit) {
      militante.addEventListener('change', function () {
        if (this.value === 'SI') {
          numMilit.setAttribute('required', 'required');
        } else {
          numMilit.removeAttribute('required');
          numMilit.value = '';
        }
      });
    }
  });
</script>
