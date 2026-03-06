<?php
function csrf_token(): string {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf'];
}

function csrf_input(): void {
  // consistente con csrf_check()
  echo '<input type="hidden" name="csrf" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">';
}

/**
 * Valida CSRF por:
 * - POST[csrf]  (default)
 * - Header: X-CSRF-Token
 *
 * Si quieres permitir GET sin CSRF, NO llames a csrf_check() en GET.
 */

function csrf_check(): void {
  $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
  if (in_array($method, ['GET','HEAD','OPTIONS'], true)) return;
  
  $tokenSession = $_SESSION['csrf'] ?? '';
  $tokenPost    = $_POST['csrf'] ?? ($_POST['_csrf'] ?? '');
  $tokenHdr     = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

  $token = $tokenPost ?: $tokenHdr;

  if ($tokenSession === '' || $token === '' || !hash_equals($tokenSession, $token)) {
    http_response_code(419);

    // Si existe tu helper json(), úsalo; si no, fallback
    if (function_exists('json')) {
      json(['error' => 'CSRF_INVALID'], 419);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'CSRF_INVALID'], JSON_UNESCAPED_UNICODE);
    exit;
  }
}