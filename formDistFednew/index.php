<?php
require_once '../helpers/errores.php'; // <— o ../header.php según tu estructura
require_once '../config/config.php'; // <— o ../header.php según tu estructura
require_once '../helpers/auth.php'; // <— o ../header.php según tu estructura
require_once '../helpers/db.php'; // <— o ../header.php según tu estructura
require_once '../helpers/url.php';

require_login();

// Página: Nuevo registro (solo formulario)
// Ajusta los require si tus rutas son distintas:
$pageTitle  = 'Nuevo registro Representante Distrital Local';
$pageHeader = 'Nuevo registro Representante Distrital Local';

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
  <a href="../filtDistFed/" class="btn btn-warning fw-bolder">Cancelar</a>
</div>

<div class="container-fluid px-4">
<form id="formCrear" name="formCrear" class="mb-4 w-100" method="POST" enctype="multipart/form-data">
 <hr>
 <?php include '../includes/fieldset.php'; 

 $estados_mexico = [
    "AGUASCALIENTES", "BAJA CALIFORNIA", "BAJA CALIFORNIA SUR", "CAMPECHE", "CHIAPAS",
    "CHIHUAHUA", "CIUDAD DE MÉXICO", "COAHUILA", "COLIMA", "DURANGO", "ESTADO DE MÉXICO",
    "GUANAJUATO", "GUERRERO", "HIDALGO", "JALISCO", "MICHOACÁN", "MORELOS", "NAYARIT",
    "NUEVO LEÓN", "OAXACA", "PUEBLA", "QUERÉTARO", "QUINTANA ROO", "SAN LUIS POTOSÍ",
    "SINALOA", "SONORA", "TABASCO", "TAMAULIPAS", "TLAXCALA", "VERACRUZ", "YUCATÁN", "ZACATECAS"
  ];

 /* Opciones de input para cada sección */
 $rep_estado = [
    ["label" => "Estado", "type" => "select", "options" => $estados_mexico],
    ["label" => "Tipo", "type" => "select", "options" => ["Propietario", "Suplente"]],
    ["label" => "Fecha Registro", "type" => "date"],
    ["label" => "Folio Formato"]
  ];

 $datos_generales = [
    ["label" => "Paterno"],
    ["label" => "Materno"],
    ["label" => "Nombre(s)"],
    ["label" => "Fecha Nacimiento", "type" => "date"],
    ["label" => "Sexo", "type" => "select", "options" => ["Mujer", "Hombre"]],
    ["label" => "Militante", "type" => "select", "options" => ["Sí", "No"]],
    ["label" => "No. Militante"],
    ["label" => "Escolaridad", "type" => "select", "options" => [
      "Educación Primaria", "Educación Secundaria", "Bachillerato", "Licenciatura",
      "Maestría", "Doctorado", "Especialidad", "Posgrado", "Diplomado",
      "Certificación Profesional", "Otros"
    ]],
    ["label" => "Participo en otras Elecciones", "type" => "select", "options" => ["Sí", "No"]],
    ["label" => "Clave de Elector"],
    ["label" => "Copia Credencial Elector", "type" => "select", "options" => ["Sí", "No"]],
    ["label" => "Observación", "type" => "text"]
  ];

  $contacto_redes = [
    ["label" => "Celular", "type" => "tel"],
    ["label" => "Particular", "type" => "tel"],
    ["label" => "Oficina", "type" => "tel"],
    ["label" => "Correo Electrónico", "type" => "email"],
    ["label" => "Facebook"],
    ["label" => "X (antes twitter)"]
  ];

  $domicilio = [
    ["label" => "Calle"],
    ["label" => "No. Ext"],
    ["label" => "No. Int"],
    ["label" => "Colonia"],
    ["label" => "C.P."],
    ["label" => "Estado Domicilio", "type" => "select", "options" => $estados_mexico],
    ["label" => "Municipio", "type" => "select", "options" => []], // ← Se llenará dinámicamente por JS
    ["label" => "Sección", "type" => "select", "options" => []]    // ← Se llenará dinámicamente por JS
  ];

  $datos_proponente = [
    ["label" => "Paterno Proponente"],
    ["label" => "Materno Proponente"],
    ["label" => "Nombre Proponente"],
    ["label" => "Cargo Proponente"],
    ["label" => "Teléfono Contacto Proponente", "type" => "tel"]
  ];
   ?>

   <div class="container-fluid px-4">
      <div class="container bg-light p-4 rounded shadow-sm">
        <?php
          /* SECCIONES DEL FORMULARIO */
          crearFieldset("Representante Distrital Federal", $rep_estado, 'rep_estado',4);
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
