<?php
require_once 'helpers/errores.php';
require_once 'helpers/url.php';

// Antes de session_start():
session_set_cookie_params([
  'lifetime' => 0,
  'path' => '/',
  'domain' => '',
  'secure' => true,     // requiere HTTPS
  'httponly' => true,
  'samesite' => 'Strict'
]);

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Tras login correcto:
#session_regenerate_id(true);

// Opcional: fijar fingerprint del agente
if (!isset($_SESSION['fingerprint'])) {
  $_SESSION['fingerprint'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . session_id());
} else {
  $fp = hash('sha256', $_SERVER['HTTP_USER_AGENT'] . session_id());
  if ($fp !== $_SESSION['fingerprint']) {
    session_destroy(); exit('Posible secuestro de sesión');
  }
}

// Evitar la caché para la respuesta de PHP (HTML generado)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Fecha en el pasado
header("Pragma: no-cache"); // Para navegadores antiguos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aviso de Privacidad</title>
    <link rel="icon" href="../img/elecciones.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <style>
        /* Ajustar márgenes y padding en dispositivos móviles */
        .container {
            margin-top: 20px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .content-section {
            margin-bottom: 20px;
            font-size: 1rem;
            line-height: 1.6;
        }

        .content-section p {
            margin-bottom: 1rem;
        }

        .btn {
            width: 100%;
            margin-top: 20px;
        }

        /* Ajustes específicos para pantallas pequeñas */
        @media (max-width: 576px) {
            h2 {
                font-size: 1.5rem;
            }

            .content-section {
                font-size: 0.9rem;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<div class="container" class="mx-auto">
    <div class="text-center"><h2>AVISO DE PRIVACIDAD</h2></div>

    <div style="text-align: justify !important; padding-bottom:10px; font-weight:bolder;">Por favor, lea nuestro aviso de privacidad antes de continuar. Al continuar, acepta las políticas y condiciones.</div>
    
    <div class="row">
        <div class="col-6">
            <form method="POST" action="">
                <button type="submit" class="btn btn-sm btn-primary">Aceptar y continuar</button>
            </form>
        </div>
        <div class="col-6">
            <a href="logout.php" class="btn btn-sm btn-danger">Cerrar sesión</a>
        </div>
    </div>

    <hr>

    <div style="text-align: justify !important; padding-bottom:30px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;De conformidad con lo establecido en los artículos 1, 2, 5, 6, 7,10, 13, 14, 15, 16,18 de la Ley Federal de Protección de Datos Personales en Posesión de los Particulares (LFPDPPP), el presente documento constituye el “Aviso de Privacidad” de “Luis Arturo Gama Castañeda” (el “Responsable”), con domicilio en Insurgentes 1a. Privada N0. 2 San Buenaventura, Toluca, México, mismo que pone a disposición de Usted (el “Titular”), previo a la obtención y tratamiento de sus datos personales.</div>

    <div style="text-align: justify !important; padding-bottom:10px;"><span><i>¿Qué son los datos personales?</i></span></div>
    <div style="text-align: justify !important; padding-bottom:30px;">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Se considera como cualquier información concerniente a una persona identificada o identificable. Se considera que una persona es identificable cuando su identidad pueda determinarse directa o indirectamente a través de cualquier información.
    </div>

    <div style="text-align: justify !important; padding-bottom:10px;"><span><i>¿Qué datos son obtenidos por este registro?</i></span></div>
    <div style="text-align: justify !important; padding-bottom:30px;">
    •	Estado de residencia
    <br>
    •	Municipio
    <br>
    •	Sección a la que pertenece en base a su municipio
    <br>
    •	Apellido paterno
    <br>
    •	Apellido materno
    <br>
    •	Nombre(s)
    <br>
    •	Fecha de nacimiento por día, mes y año
    <br>
    •	Género
    <br>
    •	Domicilio
    <br>
    •	Número exterior e interior (si es que cuenta con el segundo)
    <br>
    •	Colonia
    <br>
    •	Código postal
    <br> 
    •	Teléfono particular
    <br>
    •	Teléfono celular
    <br> 
    •	Email, Facebook, X, Instagram
    </div>

    <div style="text-align: justify !important; padding-bottom:10px;"><span><i>Finalidades del tratamiento de datos personales</i></span></div>
    <div style="text-align: justify !important; padding-bottom:30px;">
    •	Fines estadísticos y de participación
    <br>
    •	Comprobación de personas que participan en el ejercicio de este registro
    <br>
    •	Transmitir información sobre ministras y ministros de la Suprema Corte de Justicia de la Nación
    <br>
    Respetamos su derecho a la privacidad y a la protección de datos personales que usted ha compartido voluntariamente, no vendemos o rentamos sus datos personales.
    </div>

    <div style="text-align: justify !important; padding-bottom:10px;"><span><i>¿Por cuánto tiempo utilizamos sus datos?</i></span></div>
    <div style="text-align: justify !important; padding-bottom:30px;">
    Utilizamos sus datos personales durante el período estrictamente necesario para lograr los fines previstos y en los términos establecidos por la legislación aplicable.
    </div>

    <div style="text-align: justify !important; padding-bottom:10px;"><span><i>Protección de los Datos Personales</i></span></div>
    <div style="text-align: justify !important; padding-bottom:30px;">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Se adoptan las medidas de seguridad administrativas, técnicas, tecnológicas y/o físicas que permitan proteger sus datos personales a efecto de evitar cualquier daño, robo intencionado, alteración o uso indebido, así como el acceso, divulgación, modificación o destrucción que no sean autorizadas.
    <br><br>
    •	Administrativas: Políticas y procedimientos internos para garantizar la protección de los datos personales, así como la capacitación continua del personal en materia de protección de datos.
    <br>
    •	Técnicas: Uso de tecnologías avanzadas para la protección de datos, como cifrado de información, firewalls y sistemas de detección de intrusiones.
    <br>
    •	Tecnológicas: Implementación de software de seguridad y herramientas de monitoreo para prevenir accesos no autorizados y garantizar la integridad de los datos.
    <br>
    •	Físicas: Implementación de medidas de seguridad para proteger los equipos y dispositivos donde se guarda la información.
    </div>

    <div style="text-align: justify !important; padding-bottom:10px;"><span><i>Derechos ARCO</i></span></div>
    <div style="text-align: justify !important; padding-bottom:30px;">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Usted reconoce su consentimiento para el tratamiento de sus datos personales con el propósito de cumplir con las finalidades establecidas en el presente Aviso de Privacidad. De conformidad con la LFPDPPP, usted goza de los derechos para acceder, rectificar y cancelar los datos personales que ha proporcionado. Atento a lo anterior, reconoce que se le ha dado a conocer el presente Aviso de Privacidad.
    </div>

    <div style="text-align: justify !important; padding-bottom:10px;"><span><i>Transferencia de Datos a Terceros</i></span></div>
    <div style="text-align: justify !important; padding-bottom:20px;">
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sus datos personales podrán ser transferidos y tratados dentro y fuera del país, por personas distintas a esta empresa. En ese sentido, su información puede ser compartida con terceros para cumplir con las finalidades previstas en este aviso de privacidad. Nos comprometemos a no transferir su información personal a terceros sin su consentimiento, salvo las excepciones previstas en el artículo 35 de la LFPDPPP, así como realizar esta transferencia en los términos que fija esa ley.
    <br><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Si el presente aviso de privacidad sufriera cambios, se harán del conocimiento del Titular por los medio electrónicos correspondientes para que este en posibilidad de decidir refrendar o declinar su autorización.
    <br><br>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Por lo que, al hacer clic en “Aceptar y continuar” manifiesta su conformidad y consentimiento explícito para que estos sean tratados conforme a las finalidades y términos establecidos en este Aviso de Privacidad.
    </div>

    <hr>

    <div style="text-align: center !important; font-weight:bolder;">Ultima actualización 5 de abril del 2025</div>

    <hr>
</div>

</body>
</html>
