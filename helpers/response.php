<?php

function json(array $data, int $status = 200): void
{
    http_response_code($status);

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    $flags = JSON_UNESCAPED_UNICODE;

    if (defined('APP_ENV') && APP_ENV === 'dev') {
        $flags |= JSON_PRETTY_PRINT;
    }

    echo json_encode($data, $flags);
    exit;
}

function redirect($url): void {
  header("Location: $url"); exit;
}