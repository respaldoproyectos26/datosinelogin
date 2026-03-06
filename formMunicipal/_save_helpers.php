<?php
function rmAcc($s){ return strtr($s ?? '', ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
    'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','ñ'=>'n','Ñ'=>'N']); }
function norm($s){ return mb_strtoupper(trim(rmAcc($s ?? '')), 'UTF-8'); }
function digits($s,$len=null){ $s=preg_replace('/\D+/', '', $s ?? ''); return $len? mb_substr($s,0,$len):$s; }
function fecha_valida($y,$m,$d){ return checkdate((int)$m,(int)$d,(int)$y); }
function s($v){ return trim((string)($v ?? '')); }

// =====================================================
// Teléfonos: reglas de negocio
// - 10 dígitos exactos
// - no inicia con 0
// - sin patrones numéricos comunes
// =====================================================

function is_repeated_digits(string $t): bool {
  // 0000000000, 1111111111, etc.
  return preg_match('/^(\d)\1{9}$/', $t) === 1;
}

function is_sequential_digits(string $t): bool {
  // 0123456789 / 1234567890 / 9876543210 / 0987654321, etc.
  $asc1  = '0123456789';
  $asc2  = '1234567890';
  $desc1 = '9876543210';
  $desc2 = '0987654321';
  return strpos($asc1, $t) !== false
      || strpos($asc2, $t) !== false
      || strpos($desc1,$t) !== false
      || strpos($desc2,$t) !== false;
}

function is_easy_pattern(string $t): bool {
  // patrones “bonitos” comunes que suelen ser falsos:
  // 1122334455, 1212121212, 1010101010, 1231231231 (aprox), etc.
  if (preg_match('/^(\d{2})\1{4}$/', $t)) return true;        // ABABABABAB (1212121212)
  if (preg_match('/^(\d{2})(\d{2})\1\2\1\2$/', $t)) return true; // AABB AABB AA (1122334455) - heurística
  if (preg_match('/^(01|12|23|34|45|56|67|78|89|90){5}$/', $t)) return true; // 0123456789-like
  return false;
}

/**
 * Valida y normaliza teléfono.
 * @param mixed  $raw  input (puede venir con espacios, guiones, etc)
 * @param bool   $required  si es obligatorio
 * @return array [string $telNormalizado, ?string $error]  (error null si ok)
 */
function validate_tel($raw, bool $required = false): array {
  $t = digits($raw); // quita todo excepto dígitos

  if ($t === '') {
    return $required ? ['', 'Teléfono requerido'] : ['', null];
  }

  // 10 dígitos exactos
  if (strlen($t) !== 10) return [$t, 'Debe tener 10 dígitos exactos'];

  // no iniciar con 0
  if ($t[0] === '0') return [$t, 'No debe iniciar con 0'];

  // sin patrones numéricos
  if (is_repeated_digits($t)) return [$t, 'No se permiten números repetidos'];
  if (is_sequential_digits($t)) return [$t, 'No se permiten secuencias (123.../987...)'];
  if (is_easy_pattern($t)) return [$t, 'No se permiten patrones (12-12-12, 11-22-33, etc.)'];

  return [$t, null];
}

function figura_norm($v): string {
  $v = norm($v); // ya tienes norm(): quita acentos + upper + trim

  // normalizaciones típicas (sinónimos/variantes)
  $map = [
    'ESTATAL'           => 'REP ESTATAL',
    'REP ESTATAL'       => 'REP ESTATAL',
    'MUNICIPAL'         => 'REP MUNICIPAL',
    'REP MUNICIPAL'     => 'REP MUNICIPAL',
    'DISTRITAL FEDERAL' => 'REP DIST FED',
    'DISTFED'           => 'REP DIST FED',
    'REP DIST FED'      => 'REP DIST FED',
    'DISTRITAL LOCAL'   => 'REP DIST LOC',
    'DISTLOC'           => 'REP DIST LOC',
    'REP DIST LOC'      => 'REP DIST LOC',
    'RG'                => 'RG',
    'RC'                => 'RC',
    'PROMOVIDO'         => 'PROMOVIDO',
  ];

  return $map[$v] ?? $v; // si llega algo fuera del catálogo, lo deja (pero idealmente lo bloqueas abajo)
}

function figura_is_allowed(string $fig): bool {
  static $allow = [
    'REP ESTATAL'=>true,
    'REP MUNICIPAL'=>true,
    'REP DIST FED'=>true,
    'REP DIST LOC'=>true,
    'RG'=>true,
    'RC'=>true,
    'PROMOVIDO'=>true,
  ];
  return isset($allow[$fig]);
}