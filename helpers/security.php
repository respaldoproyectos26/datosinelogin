<?php
function e($str) {
  return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Placeholder simple
function rateLimit($key, $maxAttempts, $seconds) {
    $now = time();

    if (!isset($_SESSION['rate_limit'])) {
        $_SESSION['rate_limit'] = [];
    }

    // Inicializar datos para esta clave si no existen
    if (!isset($_SESSION['rate_limit'][$key])) {
        $_SESSION['rate_limit'][$key] = ['count' => 0, 'start_time' => $now];
    }

    $data = &$_SESSION['rate_limit'][$key];

    // Si ya pasó el tiempo de ventana, reiniciamos contador y tiempo
    if ($now - $data['start_time'] > $seconds) {
        $data['count'] = 0;
        $data['start_time'] = $now;
    }

    // Incrementamos contador
    $data['count']++;

    // Si se excede el máximo, devolvemos false
    if ($data['count'] > $maxAttempts) {
        return false;
    }

    return true;
}

function ea($str){ return htmlspecialchars($str, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
