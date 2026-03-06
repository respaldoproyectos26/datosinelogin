<?php
// datosinelogin/formulario/api/get_municipios.php
require_once __DIR__ . '/../../helpers/bootstrap_api.php';
require_login();

if (empty($_POST['idedo'])) {
    echo json_encode(['error' => 'Estado requerido']); exit;
  }

$idedo = trim((string)$_POST['idedo']);

try {
  $pdo = db();

  if (ctype_digit($idedo)) {
    // Por ID de entidad
    $sql = "SELECT idmunicalc AS mun_code, municalc AS municalc, municalc AS mun_name
            FROM municalc
            WHERE idedo = :edo
            ORDER BY idmunicalc";
    $st = $pdo->prepare($sql);
    $st->bindValue(':edo', (int)$idedo, PDO::PARAM_INT);
  } else {
    // Por nombre (case/espacios resistentes)
    $sql = "SELECT idmunicalc AS mun_code, municalc AS municalc, municalc AS mun_name
            FROM municalc
            WHERE UPPER(TRIM(nomestado)) = UPPER(TRIM(:edo))
            ORDER BY idmunicalc";
    $st = $pdo->prepare($sql);
    $st->bindValue(':edo', $idedo, PDO::PARAM_STR);
  }
  
  $st->execute();
  $rows = $st->fetchAll(PDO::FETCH_ASSOC);
  echo json_encode($rows ?: []);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Error en la base de datos', 'detail' => $e->getMessage()]);
}
