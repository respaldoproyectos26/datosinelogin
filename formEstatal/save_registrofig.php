<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

set_error_handler(function($severity, $message, $file, $line) {
  throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function($e) {
  http_response_code(500);
  error_log("[EXCEPTION] ".$e->getMessage()." @ ".$e->getFile().":".$e->getLine());
  echo json_encode(['ok'=>false,'msg'=>'Error servidor','error'=>$e->getMessage()]);
  exit;
});

register_shutdown_function(function() {
  $err = error_get_last();
  if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
    http_response_code(500);
    error_log("[FATAL] ".$err['message']." @ ".$err['file'].":".$err['line']);
    echo json_encode(['ok'=>false,'msg'=>'Fatal PHP','error'=>$err['message']]);
  }
});

/**
 * ✅ CLAVE: usa el mismo bootstrap que index.php
 * Esto asegura: session_start, auth(), db(), csrf_token() consistente, etc.
 */
require_once __DIR__ . '/../helpers/bootstrap.php';
require_once '_save_helpers.php';
require_once 'geo_helpers.php';

require_login();
csrf_check(); // ✅ ahora sí validas CSRF en este POST

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido']);
  exit;
}

$pdo = db();
$u = auth()->user();
$usuareg = $u['usuario'] ?? 'ADMIN';

$tabla   = 'registrorepestatal';

$id    = (int)($_POST['id'] ?? 0);    // id de registrorepestatal
$id_ru = (int)($_POST['id_ru'] ?? 0); // id en registros_usuarios_login (para UPDATE)

if ($id > 0 && $id_ru === 0) {
  $st = $pdo->prepare("SELECT id_reglogin FROM {$tabla} WHERE id=:id LIMIT 1");
  $st->execute([':id'=>$id]);
  $id_ru = (int)($st->fetchColumn() ?: 0);
}

// Log rápido
if (defined('APP_DEBUG') && APP_DEBUG) {
  file_put_contents(__DIR__.'/debug_post.log',
  "POST:\n".print_r($_POST,true)."\nFILES:\n".print_r($_FILES,true)."\n",
  FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['ok'=>false,'msg'=>'Método no permitido']);
  exit;
}

function save_uploaded_image(array $file, string $prefix): ?string {
  if (empty($file['name'])) return null;
  if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Error al subir imagen.');
  if ($file['size'] > 3*1024*1024) throw new RuntimeException('La imagen supera 3MB.');

  $finfo = new finfo(FILEINFO_MIME_TYPE);
  $mime  = $finfo->file($file['tmp_name']);
  $ext = null;
  if ($mime === 'image/jpeg') $ext = 'jpg';
  elseif ($mime === 'image/png') $ext = 'png';
  if (!$ext) throw new RuntimeException('Formato inválido (JPG/PNG).');

  $dir = __DIR__ . '/../uploads/' . date('Y/m');
  if (!is_dir($dir)) @mkdir($dir, 0775, true);

  $basename = $prefix.'_'.date('Ymd_His').'_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $dest = rtrim($dir,'/').'/'.$basename;

  if (!move_uploaded_file($file['tmp_name'], $dest)) throw new RuntimeException('No se pudo guardar la imagen.');

  return date('Y/m').'/'.$basename;
}

// ======= Datos =======
$paterno   = norm($_POST['paterno'] ?? '');
$materno   = norm($_POST['materno'] ?? '');
$nombre    = norm($_POST['nombre'] ?? '');
$estadodom = norm($_POST['estadodom'] ?? 'SAN LUIS POTOSI');

$dianac  = sprintf('%02d', (int)($_POST['dianac'] ?? 0));
$mesnac  = sprintf('%02d', (int)($_POST['mesnac'] ?? 0));
$yearnac = sprintf('%04d', (int)($_POST['yearnac'] ?? 0));
if (!fecha_valida($yearnac, $mesnac, $dianac)) {
  echo json_encode(['ok'=>false,'msg'=>'Fecha de nacimiento inválida']); exit;
}
$fechanac = "{$yearnac}-{$mesnac}-{$dianac}";

$genero    = norm($_POST['genero'] ?? '');
$claveElector  = norm($_POST['clave_elector'] ?? '');
$escolaridad = norm($_POST['escolaridad'] ?? '');
$curp = NULL;

$militante      = norm($_POST['militante'] ?? null);
$num_milit      = s($_POST['num_milit'] ?? null);
$otraelec       = norm($_POST['otraelec'] ?? null);
$copia_credelec = strtoupper(trim((string)($_POST['copia_credelec'] ?? 'NO')));
$copia_credelec = in_array($copia_credelec, ['SI','NO'], true) ? $copia_credelec : 'NO';
$afinidad       = norm($_POST['afinidad'] ?? null);
$otro           = s($_POST['otro'] ?? null);

// Domicilio (fix: el form manda "calle")
$domicilio = norm($_POST['calle'] ?? ($_POST['domicilio'] ?? ''));
$numext    = norm($_POST['numext'] ?? '');
$numint    = norm($_POST['numint'] ?? '');
$colonia   = norm($_POST['colonia'] ?? '');
$cp        = digits($_POST['cp'] ?? '', 5);
$estado    = norm($_POST['idedo'] ?? '');
$municalc = norm($_POST['idmunicalc'] ?? ($_POST['idmunicalc_locked'] ?? ''));
$seccion   = norm($_POST['seccion'] ?? '');
$seccionInt = (int)preg_replace('/\D+/', '', (string)$seccion); // ★ necesario

// Contacto
$telErrors = []; // opcional: para mensaje detallado
#$cel       = digits($_POST['cel'] ?? '', 10);
#$telpart   = digits($_POST['telpart'] ?? '', 10);
#$teloficina= digits($_POST['teloficina'] ?? '', 10);
[$cel, $e] = validate_tel($_POST['cel'] ?? '', true);
if ($e) $telErrors['cel'] = $e;
[$telpart, $e] = validate_tel($_POST['telpart'] ?? '', false);
if ($e) $telErrors['telpart'] = $e;
[$teloficina, $e] = validate_tel($_POST['teloficina'] ?? '', false);
if ($e) $telErrors['teloficina'] = $e;
$email     = s($_POST['email'] ?? '');
$facebook  = s($_POST['facebook'] ?? '');
$twitter   = s($_POST['twitter'] ?? '');
$instag    = s($_POST['instag'] ?? '');
$observacion = norm($_POST['observacion'] ?? '');
$obs = $observacion ?? '';

// Proponente / estructura
$estrucasoc = norm($_POST['estrucasoc'] ?? '');
$cargo_proponente = norm($_POST['cargo_proponente'] ?? '');
#$tel_proponente   = digits($_POST['tel_proponente'] ?? '', 10);
[$tel_proponente, $e] = validate_tel($_POST['tel_proponente'] ?? '', false);
if ($e) $telErrors['tel_proponente'] = $e;

// Representación
$estatus = s($_POST['estatus'] ?? 'ACTIVO');
$fecha_form = $_POST['fecha_form'] ?? '';
$folio_form = s($_POST['folio_form'] ?? '');
$edo_form   = s($_POST['edo_form'] ?? '');
$distfed_form = norm($_POST['distfed_form'] ?? '');
$munic_form = s($_POST['munic_form'] ?? '');
$tipo_form = s($_POST['tipo_form'] ?? '');
$num_ruta  = s($_POST['num_ruta'] ?? '');
// Defaults reales:
if ($tipo_form === '') $tipo_form = null; // o '' si tu columna permite null y quieres "sin dato"
if ($num_ruta === '')  $num_ruta  = '0';
$secciones_ruta = norm($_POST['secciones_ruta'] ?? '');

// Defaults
$tiporeg = norm($_POST['tiporeg'] ?? 'NORMAL');
$figura = figura_norm($_POST['figura'] ?? 'REP ESTATAL');
if (!figura_is_allowed($figura)) {
  echo json_encode(['ok'=>false,'msg'=>'Figura inválida']); exit;
}

$old = ['foto'=>null,'foto_ine'=>null];
if ($id > 0) {
  $stOld = $pdo->prepare("SELECT foto, foto_ine FROM {$tabla} WHERE id = :id LIMIT 1");
  $stOld->execute([':id'=>$id]);
  $old = $stOld->fetch(PDO::FETCH_ASSOC) ?: $old;
}

// Foto segura (MIME + subcarpetas por año/mes)
$fotoRuta = null;
$fotoIneRuta = null;

if (!empty($_FILES['foto']['name'])) {
  $fotoRuta = save_uploaded_image($_FILES['foto'], 'mun');
}

if (!empty($_FILES['foto_ine']['name'])) {
  $fotoIneRuta = save_uploaded_image($_FILES['foto_ine'], 'ine');
}

// ===== Reglas INE =====
if ($copia_credelec === 'NO') {
  // si NO trae INE, se borra foto_ine y se ignora cualquier upload accidental
  $fotoIneRuta = null;
} else {
  // SI trae INE:
  // - en UPDATE si no subió nueva, conserva la anterior
  if ($id > 0 && !$fotoIneRuta && !empty($old['foto_ine'])) {
    $fotoIneRuta = '__KEEP__'; // marcador para no tocar el campo
  }
  // - en INSERT si no subió, error
  if ($id === 0 && !$fotoIneRuta) {
    echo json_encode(['ok'=>false,'msg'=>'Si trae INE, debes subir la foto del INE.','faltan'=>['foto_ine']]); exit;
  }
}

if (!empty($telErrors)) {
  // Para integrarlo con tu JS markInvalid(res.faltan)
  echo json_encode([
    'ok' => false,
    'msg' => 'Teléfonos inválidos: 10 dígitos exactos, no iniciar con 0 y sin patrones numéricos.',
    'faltan' => array_keys($telErrors),   // reutilizas tu mecánica actual
    // 'errors' => $telErrors,            // si quieres ver el detalle en consola
  ]);
  exit;
}

// Reglas mínimas
$required = [
  'paterno'=>$paterno, 'materno'=>$materno, 'nombre'=>$nombre,
  'genero'=>$genero, 'dianac'=>$dianac, 'mesnac'=>$mesnac, 'yearnac'=>$yearnac,
  'calle'=>$domicilio, 'colonia'=>$colonia, 'cp'=>$cp,
  'idedo'=>$estado, 'idmunicalc'=>$municalc, 'seccion'=>$seccion,
  'cel'=>$cel
];
$faltan = [];
foreach ($required as $k=>$v) if ($v === '' || $v === null) $faltan[] = $k;
if ($faltan) { echo json_encode(['ok'=>false,'msg'=>'Faltan campos','faltan'=>$faltan]); exit; }

// Dirección y edad
$direccion = normaliza_direccion($domicilio, $numext, $numint, $colonia, $cp, $municalc, $estadodom);
$edad = (new DateTime($fechanac))->diff(new DateTime())->y;

// ===== TX =====
$pdo->beginTransaction();
try {
  if ($id === 0) {
    // Anti-duplicado
    $dup = $pdo->prepare("SELECT id FROM {$tabla}
      WHERE estado=:estado AND paterno=:paterno AND materno=:materno
        AND nombre=:nombre AND seccion=:seccion LIMIT 1");
    $dup->execute([
      ':estado'=>$estado, ':paterno'=>$paterno, ':materno'=>$materno,
      ':nombre'=>$nombre, ':seccion'=>$seccion
    ]);
    if ($dup->fetchColumn()){
      $pdo->rollBack();
      echo json_encode(['ok'=>false,'msg'=>'Registro duplicado (Estado,Paterno,Materno,Nombre,Sección).']);
      exit;
    }

    // Ubicación
    $id_ubicacion = ensure_ubicacion($pdo, $direccion);

    if ($copia_credelec === 'NO') $fotoIneRuta = null;

    // INSERT registros_usuarios_login
    $st1 = $pdo->prepare("INSERT INTO registros_usuarios_login (
      estado, municipio, figura, fechanac, edad, ine, curp, nombre, paterno, materno, telefono,
      calle, numext, numint, colonia, cp, seccion, estadodom, estatus, observaciones, estrucasoc, id_ubicacion, fecha_reg
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())");

    $st1->execute([
      $estado, $municalc, $figura, $fechanac, $edad, $claveElector, $curp, $nombre, $paterno, $materno, $cel,
      $domicilio, $numext, $numint, $colonia, $cp, $seccion, $estadodom, $estatus, $observacion, $estrucasoc, $id_ubicacion
    ]);
    $id_ru = (int)$pdo->lastInsertId();

    if ($copia_credelec === 'NO') $fotoIneRuta = null;

    // INSERT registrorepestatal (requiere que hayas agregado id_reglogin a la tabla)
    $sql = "INSERT INTO {$tabla}
      (estado, municalc, paterno, materno, nombre, fechanac, genero, calle, numext, numint, colonia, cp,
       tiporeg, figura, fechareg, seccion, telpart, cel, email, facebook, twitter, instag, usuareg,
       estrucasoc, observacion, escolaridad, participo, clave_elector, copia_credencial, cargo_proponente, tel_proponente,
       militante, num_milit, otraelec, copia_credelec, afinidad, otro, fechaform, folioform, dttofed_folio, estado_folio,
       tipo_folio, num_ruta, cadenaruta, nombre_prop, cargo_prop, tel_prop, fechareg_prop, foto, foto_ine, estadodom, teloficina,
       id_reglogin)
     VALUES
      (:estado,:municalc,:paterno,:materno,:nombre,:fechanac,:genero,:domicilio,:numext,:numint,:colonia,:cp,
       :tiporeg,:figura,NOW(),:seccion,:telpart,:cel,:email,:facebook,:twitter,:instag,:usuareg,
       :estrucasoc,:observacion,:escolaridad,:participo,:clave_elector,:copia_credencial,:cargo_proponente,:tel_proponente,
       :militante,:num_milit,:otraelec,:copia_credelec,:afinidad,:otro,:fechaform,:folioform,:dttofed_folio,:estado_folio,
       :tipo_folio,:num_ruta,:cadenaruta,:nombre_prop,:cargo_prop,:tel_prop,NOW(),:foto, :foto_ine, :estadodom,:teloficina,
       :id_reglogin)";
    $st = $pdo->prepare($sql);
    $ok = $st->execute([
      ':estado'=>$estado, ':municalc'=>$municalc, ':paterno'=>$paterno, ':materno'=>$materno,
      ':nombre'=>$nombre, ':fechanac'=>$fechanac, ':genero'=>$genero, ':domicilio'=>$domicilio,
      ':numext'=>$numext, ':numint'=>$numint, ':colonia'=>$colonia, ':cp'=>$cp,
      ':tiporeg'=>$tiporeg, ':figura'=>$figura, ':seccion'=>$seccion, ':telpart'=>$telpart, ':cel'=>$cel, ':email'=>$email,
      ':facebook'=>$facebook, ':twitter'=>$twitter, ':instag'=>$instag, ':usuareg'=>$usuareg,
      ':estrucasoc'=>$estrucasoc, ':observacion'=>$observacion, ':escolaridad'=>$escolaridad,
      ':participo'=>$otraelec, ':clave_elector'=>$claveElector, ':copia_credencial' => null,
      ':cargo_proponente'=>$cargo_proponente, ':tel_proponente'=>$tel_proponente,
      ':militante'=>$militante, ':num_milit'=>$num_milit, ':otraelec'=>$otraelec, ':copia_credelec'=>$copia_credelec,
      ':afinidad'=>$afinidad, ':otro'=>$otro, ':fechaform'=>$fecha_form, ':folioform'=>$folio_form,
      ':dttofed_folio'=>$distfed_form, ':estado_folio'=>$edo_form, ':tipo_folio'=>$tipo_form, ':num_ruta'=>$num_ruta,
      ':cadenaruta'=>$secciones_ruta, ':nombre_prop'=>$estrucasoc, ':cargo_prop'=>$cargo_proponente, ':tel_prop'=>$tel_proponente,
      ':foto'=>$fotoRuta, ':foto_ine'=>$fotoIneRuta, ':estadodom'=>$estadodom, ':teloficina'=>$teloficina, ':id_reglogin'=>$id_ru
    ]);
    $main_id = (int)$pdo->lastInsertId();

    $pdo->commit();
    echo json_encode(['ok'=>$ok, 'id'=>$main_id, 'id_reglogin'=>$id_ru, 'tabla'=>$tabla]);
    exit;

  } else {
    // UPDATE: recalcula ubicación si cambia la dirección
    // ===== Detectar cambio de dirección (UPDATE) =====
    $stDir = $pdo->prepare("SELECT calle, numext, numint, colonia, cp, municalc, estadodom, id_reglogin
                            FROM {$tabla} WHERE id = :id LIMIT 1");
    $stDir->execute([':id' => $id]);
    $prev = $stDir->fetch(PDO::FETCH_ASSOC);

    $direccion_prev = null;
    if ($prev) {
      $direccion_prev = normaliza_direccion(
        (string)$prev['calle'], (string)$prev['numext'], (string)$prev['numint'],
        (string)$prev['colonia'], (string)$prev['cp'], (string)$prev['municalc'], (string)$prev['estadodom']
      );
    }

    // Si no cambió, no recalcules ni re-ensure
    if ($direccion_prev && hash_equals($direccion_prev, $direccion)) {
      // Intenta tomar id_ubicacion desde registros_usuarios_login si existe
      if ($id_ru > 0) {
        $stUb = $pdo->prepare("SELECT id_ubicacion FROM registros_usuarios_login WHERE id=:id_ru LIMIT 1");
        $stUb->execute([':id_ru'=>$id_ru]);
        $id_ubicacion = (int)($stUb->fetchColumn() ?: 0);
      } else {
        $id_ubicacion = 0;
      }
      // Si no hay id_ubicacion por algún motivo, cae al ensure para no romper
      if ($id_ubicacion <= 0) {
        $id_ubicacion = ensure_ubicacion($pdo, $direccion);
      }
    } else {
      $id_ubicacion = ensure_ubicacion($pdo, $direccion);
    }

    $dupCel = $pdo->prepare("SELECT id FROM {$tabla} WHERE cel = :cel AND id <> :id LIMIT 1");
    $dupCel->execute([':cel'=>$cel, ':id'=>$id]);
    if ($dupCel->fetchColumn()) {
      $pdo->rollBack();
      echo json_encode(['ok'=>false,'msg'=>'El celular ya existe en este padrón.']);
      exit;
    }

    // ===== FOTO INE (UPDATE) =====
    $setFotoIne = '';
    if ($copia_credelec === 'NO') {
      $setFotoIne = ", foto_ine = NULL";
    } else {
      // SI trae INE
      if ($fotoIneRuta && $fotoIneRuta !== '__KEEP__') {
        $setFotoIne = ", foto_ine = :foto_ine";
      }
    }

    // 1) UPDATE registrorepmunicipal
    $sql = "UPDATE {$tabla} SET
      estado=:estado, municalc=:municalc, paterno=:paterno, materno=:materno, nombre=:nombre,
      fechanac=:fechanac, genero=:genero, calle=:domicilio, numext=:numext, numint=:numint,
      colonia=:colonia, cp=:cp, tiporeg=:tiporeg, figura=:figura, seccion=:seccion, telpart=:telpart,
      cel=:cel, email=:email, facebook=:facebook, twitter=:twitter, instag=:instag, usuareg=:usuareg,
      estrucasoc=:estrucasoc, observacion=:observacion, escolaridad=:escolaridad,
      clave_elector=:clave_elector, copia_credencial=:copia_credencial,
      cargo_proponente=:cargo_proponente, tel_proponente=:tel_proponente,
      militante=:militante, num_milit=:num_milit, otraelec=:otraelec, copia_credelec=:copia_credelec,
      afinidad=:afinidad, otro=:otro, fechaform=:fechaform, folioform=:folioform,
      dttofed_folio=:dttofed_folio, estado_folio=:estado_folio, tipo_folio=:tipo_folio,
      num_ruta=:num_ruta, cadenaruta=:cadenaruta, nombre_prop=:nombre_prop,
      cargo_prop=:cargo_prop, tel_prop=:tel_prop, estadodom=:estadodom, teloficina=:teloficina"
      . ($fotoRuta ? ", foto = :foto" : "")
      . $setFotoIne
      . " WHERE id = :id";
    
      $params = [
        ':estado'=>$estado, ':municalc'=>$municalc, ':paterno'=>$paterno, ':materno'=>$materno,
        ':nombre'=>$nombre, ':fechanac'=>$fechanac, ':genero'=>$genero, ':domicilio'=>$domicilio,
        ':numext'=>$numext, ':numint'=>$numint, ':colonia'=>$colonia, ':cp'=>$cp,
        ':tiporeg'=>$tiporeg, ':figura'=>$figura, ':seccion'=>$seccion, ':telpart'=>$telpart,
        ':cel'=>$cel, ':email'=>$email, ':facebook'=>$facebook, ':twitter'=>$twitter, ':instag'=>$instag,
        ':usuareg'=>$usuareg, ':estrucasoc'=>$estrucasoc, ':observacion'=>$observacion,
        ':escolaridad'=>$escolaridad, ':clave_elector'=>$claveElector, ':copia_credencial' => null,
        ':cargo_proponente'=>$cargo_proponente, ':tel_proponente'=>$tel_proponente, ':militante'=>$militante,
        ':num_milit'=>$num_milit, ':otraelec'=>$otraelec, ':copia_credelec'=>$copia_credelec,
        ':afinidad'=>$afinidad, ':otro'=>$otro, ':fechaform'=>$fecha_form, ':folioform'=>$folio_form,
        ':dttofed_folio'=>$distfed_form, ':estado_folio'=>$edo_form, ':tipo_folio'=>$tipo_form,
        ':num_ruta'=>$num_ruta, ':cadenaruta'=>$secciones_ruta, ':nombre_prop'=>$estrucasoc,
        ':cargo_prop'=>$cargo_proponente, ':tel_prop'=>$tel_proponente, ':estadodom'=>$estadodom,
        ':teloficina'=>$teloficina, ':id'=>$id
      ];

      if ($fotoRuta) {
        $params[':foto'] = $fotoRuta;
      }
      if ($copia_credelec === 'SI' && $fotoIneRuta && $fotoIneRuta !== '__KEEP__') {
        $params[':foto_ine'] = $fotoIneRuta;
      }

      $ok1 = $pdo->prepare($sql)->execute($params);

    // 2) UPDATE registros_usuarios_login (usa su propio id!)
    if ($id_ru > 0) {
      $dupCelRU = $pdo->prepare("
        SELECT id 
        FROM registros_usuarios_login 
        WHERE telefono = :cel AND id <> :id_ru 
        LIMIT 1
      ");
      $dupCelRU->execute([':cel'=>$cel, ':id_ru'=>$id_ru]);

      if ($dupCelRU->fetchColumn()) {
        $pdo->rollBack();
        echo json_encode(['ok'=>false,'msg'=>'El celular ya existe en registros_usuarios_login.']);
        exit;
      }
      $sql2 = "UPDATE registros_usuarios_login SET 
        estado=:estado, municipio=:municipio, figura=:figura, fechanac=:fechanac, edad=:edad,
        ine=:ine, curp=:curp, nombre=:nombre, paterno=:paterno, materno=:materno, telefono=:telefono,
        calle=:calle, numext=:numext, numint=:numint, colonia=:colonia, cp=:cp, seccion=:seccion,
        estadodom=:estadodom, estatus=:estatus, observaciones=:observaciones, estrucasoc=:estrucasoc,
        id_ubicacion=:id_ubicacion
        WHERE id = :id_ru";
      $ok2 = $pdo->prepare($sql2)->execute([
        ':estado'=>$estado, ':municipio'=>$municalc, ':figura'=>$figura, ':fechanac'=>$fechanac,
        ':edad'=>$edad, ':ine'=>$claveElector, ':curp'=>$curp, ':nombre'=>$nombre, ':paterno'=>$paterno,
        ':materno'=>$materno, ':telefono'=>$cel, ':calle'=>$domicilio, ':numext'=>$numext,
        ':numint'=>$numint, ':colonia'=>$colonia, ':cp'=>$cp, ':seccion'=>$seccion,
        ':estadodom'=>$estadodom, ':estatus'=>$estatus, ':observaciones'=>$observacion, ':estrucasoc'=>$estrucasoc,
        ':id_ubicacion'=>$id_ubicacion, ':id_ru'=>$id_ru
      ]);
    } else {
      $ok2 = true;
    }

    $pdo->commit();
    echo json_encode(['ok'=>$ok1 && $ok2, 'id'=>$id, 'id_reglogin'=>$id_ru]);
    exit;
  }

} catch (PDOException $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  if ((int)$e->getCode() === 23000 || strpos($e->getMessage(), '1062') !== false) {
    echo json_encode(['ok'=>false,'msg'=>'Duplicado (índice único).']); exit;
  }
  http_response_code(500);
  error_log("[save_registrofig] ".$e->getMessage());
  echo json_encode(['ok'=>false,'msg'=>'Error BD','error'=>$e->getMessage()]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(500);
  error_log("[save_registrofig] ".$e->getMessage());
  echo json_encode(['ok'=>false,'msg'=>'Error','error'=>$e->getMessage()]);
}