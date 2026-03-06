<?php
function validar(array $data, array $reglas): array {
  $errores = [];
  foreach ($reglas as $campo => $regla) {
    $valor = $data[$campo] ?? '';
    foreach (explode('|', $regla) as $r) {
      if ($r === 'required' && ($valor === '' || $valor === null)) {
        $errores[] = "$campo es obligatorio";
      } elseif (str_starts_with($r,'min:')) {
        $min = (int)explode(':',$r)[1];
        if (mb_strlen((string)$valor) < $min) $errores[] = "$campo debe tener al menos $min caracteres";
      } elseif ($r === 'email' && $valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "$campo no es un email válido";
      } elseif ($r === 'numeric' && $valor !== '' && !preg_match('/^\d+$/', (string)$valor)) {
        $errores[] = "$campo debe ser numérico";
      }
    }
  }
  return $errores;
}
