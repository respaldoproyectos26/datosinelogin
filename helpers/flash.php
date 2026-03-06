<?php
function flash($type, $msg) {
  if (session_status() !== PHP_SESSION_ACTIVE) return;
  $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function render_flash_swals() {
  if (session_status() !== PHP_SESSION_ACTIVE) return;
  if (empty($_SESSION['flash'])) return;
  echo "<script>document.addEventListener('DOMContentLoaded', ()=>{";
  foreach ($_SESSION['flash'] as $f) {
    echo "Swal.fire({icon:'{$f['type']}', text: ".json_encode($f['msg'])."});";
  }
  echo "});</script>";
  unset($_SESSION['flash']);
}
