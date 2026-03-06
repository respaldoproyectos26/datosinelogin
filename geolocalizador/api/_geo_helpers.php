<?php
require_once __DIR__ . '/../../config/keys.php'; // ← contiene GEOCODING_API_KEY

/**
 * Geocodifica una dirección con la API de Google.
 * Devuelve ['lat'=>float,'lng'=>float] o null si falla.
 */
function geocode_address(string $address): ?array {
  if (trim($address) === '') return null;
  $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='
       . urlencode($address)
       . '&key=' . urlencode(GEOCODING_API_KEY);

  $ch = curl_init($url);
if ($ch === false) return null;

try {
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 12,
    CURLOPT_SSL_VERIFYPEER => true
  ]);

  $resp = curl_exec($ch);
  if ($resp === false) {
    error_log('GeoAPI error: ' . curl_error($ch));
    return null;
  }
} finally {
  curl_close($ch);
}

  $js = json_decode($resp, true);
  if (($js['status'] ?? '') !== 'OK') {
    error_log("Geocode fallo '{$address}': " . ($js['status'] ?? 'SIN_STATUS'));
    return null;
  }
  $loc = $js['results'][0]['geometry']['location'] ?? null;
  return $loc ? ['lat' => (float)$loc['lat'], 'lng' => (float)$loc['lng']] : null;
}
