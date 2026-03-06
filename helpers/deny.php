<?php
function deny_access(
  string $msg = 'Tu sesión no tiene permisos para acceder a esta sección',
  string $redirect = 'login.php'
) {
  flash('error', $msg);
  header('Location: ' . app_url($redirect));
  exit;
}