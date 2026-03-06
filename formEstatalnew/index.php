<?php
require_once '../helpers/errores.php'; // <— o ../header.php según tu estructura
require_once '../config/config.php'; // <— o ../header.php según tu estructura
require_once '../helpers/auth.php'; // <— o ../header.php según tu estructura
require_once '../helpers/db.php'; // <— o ../header.php según tu estructura
require_once '../helpers/url.php';

// Página: Nuevo registro (solo formulario)
// Ajusta los require si tus rutas son distintas:
$pageTitle  = 'Nuevo registro Representante Estatal';
$pageHeader = 'Nuevo registro Representante Estatal';

$customStyles = [
  '<link rel="stylesheet" href="../assets/css/fieldset.css">',
];

$customScripts = [
  '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>',
  '<script src="js/script.js"></script>',
];

require_once '../includes/headerfiltrodatos.php'; // <— o ../header.php según tu estructura
// Si tu nav para este módulo es otro, cámbialo arriba del main.
?>

<div class="d-flex align-items-center w-100 justify-content-center mb-3">
  <a href="../filtEstatal/" class="btn btn-warning fw-bolder">Cancelar</a>
</div>

<div class="container-fluid px-4">
  <form id="formCrear" name="formCrear" class="mb-4 w-100" method="POST" enctype="multipart/form-data">
  <hr>
  <?php
  ########################################################################################################################
  ########################################################################################################################
  
  include '../includes/fieldset.php'; 
  
  ########################################### Constantes por input ###########################################
  $estados_mexico = [
      "AGUASCALIENTES", "BAJA CALIFORNIA", "BAJA CALIFORNIA SUR", "CAMPECHE", "CHIAPAS",
      "CHIHUAHUA", "CIUDAD DE MEXICO", "COAHUILA", "COLIMA", "DURANGO", "ESTADO DE MEXICO",
      "GUANAJUATO", "GUERRERO", "HIDALGO", "JALISCO", "MICHOACAN", "MORELOS", "NAYARIT",
      "NUEVO LEON", "OAXACA", "PUEBLA", "QUERETARO", "QUINTANA ROO", "SAN LUIS POTOSI",
      "SINALOA", "SONORA", "TABASCO", "TAMAULIPAS", "TLAXCALA", "VERACRUZ", "YUCATAN", "ZACATECAS"
    ];
  $figura =["Propietario", "Suplente"];
  $genero =["Mujer", "Hombre"];
  $logica=["Sí", "No"];
  $escolaridad=["Educación Primaria", "Educación Secundaria", "Bachillerato", "Licenciatura",
                "Maestría", "Doctorado", "Especialidad", "Posgrado", "Diplomado",
                "Certificación Profesional", "Otros"];

  ########################################### Opciones de input para cada sección ###########################################
  $rep_estado = [
      ["label" => "Estado", "name" => "estado", "type" => "select", "options" => $estados_mexico],
      ["label" => "Tipo", "name" => "figura", "type" => "select", "options" => $figura],
      ["label" => "Fecha Registro", "name" => "fechareg", "type" => "date"],
      ["label" => "Folio Formato", "name" => "folioform"]
    ];

  $datos_generales = [
      ["label" => "Paterno", "name" => "paterno", 'placeholder' => 'Apellido Paterno',
      'value' => '', 'required' => true, 'disabled' => false],
      ["label" => "Materno", "name" => "materno", 'placeholder' => 'Apellido Materno', 'required' => true],
      ["label" => "Nombre(s)", "name" => "nombre", 'placeholder' => 'Nombre(s)', 'required' => true],
      ["label" => "Fecha Nacimiento", "name" => "fechanac", "type" => "date"],
      ["label" => "Género", "name" => "genero", "type" => "select", "options" => $genero],
      ["label" => "Militante", "name" => "militante", "type" => "select", "options" => $logica],
      ["label" => "No. Militante", "name" => "num_milit", 'placeholder' => '...'],
      ["label" => "Escolaridad", "name" => "escolaridad", "type" => "select", "options" => $escolaridad],
      ["label" => "Participó en otras Elecciones", "name" => "participo", "type" => "select", "options" => $logica],
      ["label" => "Clave de Elector", "name" => "clave_elector"],
      ["label" => "Copia Credencial Elector", "type" => "select", "options" => $logica],
      ["label" => "Observación", "name" => "observacion"]
    ];

    $contacto_redes = [
      ["label" => "Celular", "name" => "cel", 'type' => 'text', 'subtype' => 'telefono', 'required' => true],
      ["label" => "Particular", "name" => "telpart", "type" => "tel"],
      ["label" => "Oficina", "name" => "teloficina", "type" => "tel"],
      ["label" => "Correo Electrónico", "name" => "email", "type" => "email"],
      ["label" => "Facebook", "name" => "facebook"],
      ["label" => "X (antes twitter)", "name" => "twitter"],
      ["label" => "Instagram", "name" => "instag"]
    ];

    $domicilio = [
      ["label" => "Calle", "name" => "calle", 'required' => true],
      ["label" => "No. Ext", "name" => "numext", "id" => "numext", 'required' => true],
      ["label" => "No. Int", "name" => "numint", "id" => "numint"],
      ["label" => "Colonia", "name" => "colonia", "id" => "colonia", 'required' => true],
      ["label" => "C.P.", "name" => "cp", 'required' => true],
      ["label" => "Estado Domicilio", "name" => "estadodom","type" => "select", 'required' => true, "options" => $estados_mexico],
      ["label" => "Municipio", "name" => "municalc", "type" => "select", 'required' => true, "options" => []], // ← Se llenará dinámicamente por JS
      ["label" => "Sección", "name" => "seccion", "type" => "select", 'required' => true, "options" => []]    // ← Se llenará dinámicamente por JS
    ];

    $datos_proponente = [
      ["label" => "Paterno Proponente", "name" => "paterno_prop", 'required' => true],
      ["label" => "Materno Proponente", "name" => "", 'required' => true],
      ["label" => "Nombre Proponente", "name" => "", 'required' => true],
      ["label" => "Cargo Proponente", "name" => "", 'required' => true],
      ["label" => "Teléfono Contacto Proponente", "name" => "", "type" => "tel"]
    ];

    ########################################################################################################################
    ########################################################################################################################
    ?>

    <div class="container-fluid px-4">
        <div class="container bg-light p-4 rounded shadow-sm">
          <?php
            /* SECCIONES DEL FORMULARIO */
            crearFieldset("Representante Estatal", $rep_estado, 'rep_estado',4);
            crearFieldset("Datos Generales", $datos_generales, 'datos_generales', 3);
            crearFieldset("Contacto y Redes Sociales", $contacto_redes, 'contacto_redes',4);
            crearFieldset("Domicilio", $domicilio, 'domicilio',3);
            crearFieldset("Datos de quien lo Propone", $datos_proponente, 'datos_proponente',3);
          ?>
        </div>
      </div>

  <div class="d-grid col-md-2 mx-auto my-3 mb-4">
      <button type="submit" class="btn btn-success btn-lg">Registrar</button>
  </div>
  </form>
</div>

<hr>

<?php require_once '../includes/footerfiltrodatos.php'; ?>
