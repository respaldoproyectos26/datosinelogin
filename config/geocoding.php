<?php
require_once __DIR__ . '/keys.php';

// Compat legacy (si algún archivo viejo usa GOOGLE_GEOCODE_KEY)
if (!defined('GOOGLE_GEOCODE_KEY')) {
  define('GOOGLE_GEOCODE_KEY', GEOCODING_API_KEY);
}

if (!defined('GEOCODE_BASE_DELAY_MS')) define('GEOCODE_BASE_DELAY_MS', 250);
if (!defined('GEOCODE_MAX_BACKOFF_SEC')) define('GEOCODE_MAX_BACKOFF_SEC', 16);