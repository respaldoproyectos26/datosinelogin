<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/url.php';     // para base_url()
require_once __DIR__ . '/../config/config.php';

/* -------------------------------------------------------
   Helpers para gestión de medios y enlaces seguros
   ------------------------------------------------------- */

if (!function_exists('str_starts_with')) {
  function str_starts_with($hay, $nee) {
    return substr($hay, 0, strlen($nee)) === $nee;
  }
}

/* ==== Dominios seguros permitidos ==== */
function media_allowed_domains(): array {
  return [
    'youtube.com','youtu.be',
    'vimeo.com',
    'onedrive.live.com','1drv.ms',
    'dropbox.com','dropboxusercontent.com'
  ];
}

/* ==== Validación de links ==== */
function media_validate_link(string $url): bool {
  $host = parse_url($url, PHP_URL_HOST);
  if (!$host) return false;
  foreach (media_allowed_domains() as $d) {
    if (str_contains($host, $d)) return true;
  }
  return false;
}

/* ==== Generar embed según el dominio ==== */
function media_embed_link(string $url): string {
  $host = parse_url($url, PHP_URL_HOST) ?: '';
  $q = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

  // YouTube
  if (str_contains($host, 'youtube.com') || str_contains($host, 'youtu.be')) {
    if (preg_match('~youtu\.be/([^?]+)~', $url, $m)) {
      $id = $m[1];
    } elseif (preg_match('~v=([^&]+)~', $url, $m)) {
      $id = $m[1];
    } else {
      $id = null;
    }
    if ($id) {
      return "<iframe src=\"https://www.youtube.com/embed/{$id}\" allowfullscreen></iframe>";
    }
  }

  // Vimeo
  if (str_contains($host, 'vimeo.com') && preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
    $id = $m[1];
    return "<iframe src=\"https://player.vimeo.com/video/{$id}\" allowfullscreen></iframe>";
  }

  // Dropbox (modo raw)
  if (str_contains($host, 'dropbox')) {
    $dl = preg_replace('~\?dl=0$~', '?raw=1', $url);
    return "<iframe src=\"" . htmlspecialchars($dl, ENT_QUOTES, 'UTF-8') . "\"></iframe>";
  }

  // Fallback: link seguro
  return "<a class='btn btn-primary' href=\"{$q}\" target='_blank' rel='noopener'>Abrir recurso</a>";
}

/* ==== Conteo de pendientes (placeholder) ==== */
if (!function_exists('exams_pending_count')) {
  function exams_pending_count(): int {
    return is_logged_in() ? 0 : 0;
  }
}

/* ==== Estructura de almacenamiento ==== */
function media_storage_dir(): string {
  // /capacitacion/biblioteca/uploads
  return path_join(APP_ROOT, 'capacitacion/biblioteca/uploads');
}

function media_base_url(): string {
  return base_url('capacitacion/biblioteca/uploads');
}

function media_subfolder_for(string $mime_or_link): string {
  if ($mime_or_link === 'link') return 'enlaces';
  if (str_starts_with($mime_or_link, 'image')) return 'imagenes';
  if ($mime_or_link === 'application/pdf') return 'pdf';
  if (str_contains($mime_or_link, 'video')) return 'videos';
  return 'otros';
}

function media_fs_path(string $filename, string $type): string {
  return path_join(media_storage_dir(), media_subfolder_for($type), $filename);
}

function media_url(string $filename, string $type): string {
  return path_join(media_base_url(), media_subfolder_for($type), $filename);
}

/* ==== Crear carpetas si no existen ==== */
function ensure_media_dirs(): void {
  $base = media_storage_dir();
  foreach (['imagenes','pdf','videos','enlaces','otros'] as $sub) {
    $dir = path_join($base, $sub);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
  }
}
