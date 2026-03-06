<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../errores.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/_save_helpers.php';
require_once __DIR__ . '/../db.php'; 

$pdo = db(); 
$u = auth()->user(); 
$usuareg = $u['usuario'] ?? 'ADMIN';

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false,'msg'=>'Método no permitido']);
    exit;
  }

  // ===== Inputs =====
  $estado    = $norm($_POST['idedo'] ?? '');
  $municalc  = $norm($_POST['idmunicalc'] ?? '');
  $paterno   = $norm($_POST['paterno'] ?? '');
  $materno   = $norm($_POST['materno'] ?? '');
  $nombre    = $norm($_POST['nombre'] ?? '');

  $dianac  = sprintf('%02d', (int)($_POST['dianac'] ?? 0));
  $mesnac  = sprintf('%02d', (int)($_POST['mesnac'] ?? 0));
  $yearnac = sprintf('%04d', (int)($_POST['yearnac'] ?? 0));
  $fechanac= "{$yearnac}-{$mesnac}-{$dianac}";
  if (!checkdate((int)$mesnac,(int)$dianac,(int)$yearnac)) {
    echo json_encode(['ok'=>false,'msg'=>'Fecha de nacimiento inválida']); exit;
  }

  $genero    = $norm($_POST['genero'] ?? '');
  $domicilio = $norm($_POST['domicilio'] ?? '');
  $numext    = $norm($_POST['numext'] ?? '');
  $numint    = $norm($_POST['numint'] ?? '');
  $colonia   = $norm($_POST['colonia'] ?? '');
  $cp        = $digits($_POST['cp'] ?? '', 5);
  $tiporeg   = $norm($_POST['tiporeg'] ?? 'NORMAL');
  $figura    = $norm($_POST['figura'] ?? 'RG');
  $seccion   = $norm($_POST['seccion'] ?? '');
  $telpart   = $digits($_POST['telpart'] ?? '', 10);
  $cel       = $digits($_POST['cel'] ?? '', 10);
  $email     = trim($_POST['email'] ?? '');
  $facebook  = trim($_POST['facebook'] ?? '');
  $twitter   = trim($_POST['twitter'] ?? '');
  $instag    = trim($_POST['instag'] ?? '');
  $estructasoc = $norm($_POST['estructasoc'] ?? '');
  $observacion = $norm($_POST['observacion'] ?? '');
  $escolaridad = $norm($_POST['escolaridad'] ?? '');
  $participo   = $norm($_POST['participo'] ?? '');
  $clave_elector = $norm($_POST['clave_elector'] ?? '');
  $cargo_proponente = $estado;
  $tel_proponente   = $digits($_POST['tel_proponente'] ?? '', 10);

  // Normalizadores (si no los tienes en _save_helpers.php):
  $norm = function($s){ return trim(mb_strtoupper($s ?? '', 'UTF-8')); };
  $digits = function($s, $len=null){
    $s = preg_replace('/\D+/', '', (string)$s);
    return $len ? mb_substr($s, 0, $len) : $s;
  };

  // ===== Inputs (agregados/corregidos) =====
  $clave_elector  = $norm($_POST['clave_elector'] ?? ''); // corregido name
  $militante      = $norm($_POST['militante'] ?? null);
  $num_milit      = trim($_POST['num_milit'] ?? null);
  $otraelec       = $norm($_POST['otraelec'] ?? null);
  $copia_credelec = $norm($_POST['copia_credelec'] ?? null);
  $afinidad       = $norm($_POST['afinidad'] ?? null);
  $otro           = trim($_POST['otro'] ?? null); // texto libre si afinidad = 'otro'

  // ===== Foto (opcional) =====
  $fotoRuta = null;
  if (!empty($_FILES['foto']['name'])) {
    $file = $_FILES['foto'];
    if ($file['error'] === UPLOAD_ERR_OK) {
      $allowed = ['jpg','jpeg','png'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed)) {
        echo json_encode(['ok'=>false,'msg'=>'Formato de foto inválido (JPG/PNG).']); exit;
      }
      if ($file['size'] > 3*1024*1024) { // 3MB
        echo json_encode(['ok'=>false,'msg'=>'La foto supera 3MB.']); exit;
      }
      $dir = realpath(__DIR__ . '/../uploads');
      if (!$dir) {
        $dir = __DIR__ . '/../uploads';
        @mkdir($dir, 0775, true);
      }
      $basename = 'rg_'.date('Ymd_His').'_' . bin2hex(random_bytes(4)) . '.' . $ext;
      $dest = rtrim($dir,'/').'/'.$basename;
      if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['ok'=>false,'msg'=>'No se pudo guardar la foto.']); exit;
      }
      $fotoRuta = $basename; // guardamos solo el nombre; el <img> arma ../uploads/$foto
    } else {
      echo json_encode(['ok'=>false,'msg'=>'Error al subir la foto.']); exit;
    }
  }

  // ===== Mapeo figura → tabla =====
  # $map = ['RG'=>'registrofig','RC'=>'registrofig2','PROMOVIDO'=>'registrofig3'];
  # $tabla = $map[$figura] ?? 'registrofig';
  $tabla = 'registrofig';

  // ===== Anti-duplicado (consulta previa) =====
  $dup = $pdo->prepare("SELECT id FROM {$tabla}
                        WHERE estado=:estado AND paterno=:paterno AND materno=:materno
                          AND nombre=:nombre AND seccion=:seccion LIMIT 1");
  $dup->execute([
    ':estado'=>$estado, ':paterno'=>$paterno, ':materno'=>$materno,
    ':nombre'=>$nombre, ':seccion'=>$seccion
  ]);
  if ($dup->fetchColumn()){
    echo json_encode(['ok'=>false,'msg'=>'Registro duplicado (Estado, Paterno, Materno, Nombre, Sección).']); exit;
  }

  // ===== Insert (ampliado) =====
  $sql = "INSERT INTO {$tabla}
    (estado, municalc, paterno, materno, nombre, fechanac, genero, domicilio, numext, numint, colonia, cp,
    tiporeg, figura, fechareg, seccion, telpart, cel, email, facebook, twitter, instag, usuareg,
    estrucasoc, observacion, escolaridad, participo, clave_elector, copia_credencial, cargo_proponente, tel_proponente,
    militante, num_milit, otraelec, copia_credelec, afinidad, otro, fechaform, foto)
    VALUES
    (:estado,:municalc,:paterno,:materno,:nombre,:fechanac,:genero,:domicilio,:numext,:numint,:colonia,:cp,
    :tiporeg,:figura,NOW(),:seccion,:telpart,:cel,:email,:facebook,:twitter,:instag,:usuareg,
    :estructasoc,:observacion,:escolaridad,:participo,:clave_elector,NULL,:cargo_proponente,:tel_proponente,
    :militante,:num_milit,:otraelec,:copia_credelec,:afinidad,:otro,CURDATE(),:foto)";

  $st = $pdo->prepare($sql);
  $ok = $st->execute([
    ':estado'=>$estado, ':municalc'=>$municalc, ':paterno'=>$paterno, ':materno'=>$materno,
    ':nombre'=>$nombre, ':fechanac'=>$fechanac, ':genero'=>$genero, ':domicilio'=>$domicilio,
    ':numext'=>$numext, ':numint'=>$numint, ':colonia'=>$colonia, ':cp'=>$cp, ':tiporeg'=>$tiporeg,
    ':figura'=>$figura, ':seccion'=>$seccion, ':telpart'=>$telpart, ':cel'=>$cel, ':email'=>$email,
    ':facebook'=>$facebook, ':twitter'=>$twitter, ':instag'=>$instag, ':usuareg'=>$usuareg,
    ':estructasoc'=>$estructasoc, ':observacion'=>$observacion, ':escolaridad'=>$escolaridad,
    ':participo'=>$participo, ':clave_elector'=>$clave_elector,
    ':cargo_proponente'=>$cargo_proponente, ':tel_proponente'=>$tel_proponente,

    ':militante'=>$militante, ':num_milit'=>$num_milit,
    ':otraelec'=>$otraelec, ':copia_credelec'=>$copia_credelec,
    ':afinidad'=>$afinidad, ':otro'=>$otro,
    ':foto'=>$fotoRuta
  ]);

  echo json_encode([
    'ok'   => $ok,
    'id'   => $ok ? $pdo->lastInsertId() : null,
    'tabla'=> $tabla
  ]);

} catch (PDOException $e) {
  // Duplicado (si agregas UNIQUE en BD)
  if ((int)$e->getCode() === 23000 || strpos($e->getMessage(), '1062') !== false) {
    echo json_encode(['ok'=>false,'msg'=>'Duplicado (índice único).']); exit;
  }
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'Error BD','error'=>$e->getMessage()]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'msg'=>'Error interno','error'=>$e->getMessage()]);
}
