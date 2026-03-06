<?php
// helpers/errores_api.php
declare(strict_types=1);

$debug = (defined('APP_DEBUG') && APP_DEBUG);

require_once __DIR__ . '/errores.php';

if (!headers_sent()) {
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
}

/**
 * Envía una respuesta JSON homogénea de error (una sola vez).
 */
function api_fail(string $msg = 'Error del servidor', int $status = 500): void {
  if (!headers_sent()) http_response_code($status);

  // Evita mezclar salida previa con JSON final
  // (si ya hubo output, al menos cerramos con algo consistente)
  $payload = ['ok' => false, 'msg' => $msg];

  echo json_encode($payload, JSON_UNESCAPED_UNICODE);
  exit;
}

/**
 * Error handler: registra y opcionalmente convierte a excepción para cortar ejecución.
 */
set_error_handler(function(int $severity, string $message, string $file, int $line): bool {
  // Respeta el nivel actual de error_reporting (si está silenciado con @ o config)
  if (!(error_reporting() & $severity)) {
    return true;
  }

  error_log("[PHP:$severity] $message in $file:$line");

  // Para APIs suele convenir detener el endpoint ante cualquier warning/notice relevante:
  // Si quieres ser más estricto, deja esto siempre.
  // Si quieres ser más permisivo, limita a E_WARNING/E_USER_WARNING o más.
  throw new ErrorException($message, 0, $severity, $file, $line);
});

/**
 * Exception handler: salida homogénea.
 */
set_exception_handler(function(Throwable $e): void {
  error_log('[EXC] '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
  api_fail('Error del servidor', 500);
});

/**
 * Shutdown handler: captura fatales que no pasan por error_handler.
 */
register_shutdown_function(function(): void {
  $err = error_get_last();
  if (!$err) return;

  $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
  if (in_array($err['type'], $fatalTypes, true)) {
    error_log("[FATAL:{$err['type']}] {$err['message']} in {$err['file']}:{$err['line']}");

    // Si ya hubo salida, igual respondemos; si está corrupta, al menos el cliente ve ok:false
    if (!headers_sent()) {
      http_response_code(500);
      header('Content-Type: application/json; charset=utf-8');
      header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }

    echo json_encode(['ok' => false, 'msg' => 'Error del servidor'], JSON_UNESCAPED_UNICODE);
  }
});
