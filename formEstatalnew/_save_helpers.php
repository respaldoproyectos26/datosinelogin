<?php
function rmAcc($s){ return strtr($s ?? '', ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','ñ'=>'n','Ñ'=>'N']); }
function norm($s){ return mb_strtoupper(trim(rmAcc($s ?? '')), 'UTF-8'); }
function digits($s,$len=null){ $s=preg_replace('/\D+/', '', $s ?? ''); return $len? mb_substr($s,0,$len):$s; }
function fecha_valida($y,$m,$d){ return checkdate((int)$m,(int)$d,(int)$y); }
