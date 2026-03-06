<?php
// Completar campos para evitar errores de name e id
function completarCampos(&$campos) {
  foreach ($campos as &$campo) {
    if (is_string($campo)) {
      $campo = ['label' => $campo];
    }
    if (empty($campo['name'])) {
      $campo['name'] = strtolower(str_replace([' ', '.', '(', ')'], ['_', '', '', ''], $campo['label']));
    }
    if (empty($campo['id'])) {
      $campo['id'] = $campo['name'];
    }
  }
}

function crearFieldset($titulo, $campos, $id = '', $cols = 2) {
  completarCampos($campos);
  $fieldset_id = $id ? "id='$id'" : '';
  $col_class = 'col-12 col-md-' . intval(12 / $cols);

  echo "<fieldset class='mb-4 border rounded-3 p-3 bg-white w-100' $fieldset_id>";
  echo "<legend class='fs-5 fw-bold mb-3'>$titulo</legend>";
  echo "<div class='row gx-3 gy-2'>";

  foreach ($campos as $campo) {
    if (is_string($campo)) {
      $campo = ["label" => $campo];
    }

    $label = $campo['label']; $name = $campo['name']; $id_input = $campo['id'];
    $type = $campo['type'] ?? 'text';
    $options = $campo['options'] ?? [];
    $value = $campo['value'] ?? '';
    $required = !empty($campo['required']) ? 'required' : '';
    $placeholder = isset($campo['placeholder']) ? "placeholder='{$campo['placeholder']}'" : '';
    $disabled = !empty($campo['disabled']) ? 'disabled' : '';

    echo "<div class='$col_class'>";
    echo "<label for='$id_input' class='form-label fw-semibold'>$label:</label>";

    if ($type === 'select') {
      echo "<select name='$name' id='$id_input' class='form-select' $required $disabled>";
      $ph = $campo['placeholder'] ?? 'Seleccione';
      echo "<option value='' disabled selected>$ph</option>";
      foreach ($options as $opt) {
        $selected = ($opt == $value) ? 'selected' : '';
        echo "<option value='$opt' $selected>$opt</option>";
      }
      echo "</select>";

    } elseif ($type === 'radio') {
      foreach ($options as $opt) {
        $radio_id = $id_input . '_' . strtolower($opt);
        $checked = ($opt == $value) ? 'checked' : '';
        echo "<div class='form-check'>";
        echo "<input class='form-check-input' type='radio' name='$name' id='$radio_id' value='$opt' $checked $required $disabled>";
        echo "<label class='form-check-label' for='$radio_id'>$opt</label>";
        echo "</div>";
      }

    } elseif ($type === 'textarea') {
      echo "<textarea name='$name' id='$id_input' class='form-control' rows='3' $required $placeholder $disabled>$value</textarea>";

    } elseif ($type === 'file') {
      $accept = isset($campo['accept']) ? "accept='{$campo['accept']}'" : '';
      $multiple = !empty($campo['multiple']) ? 'multiple' : '';
      echo "<input type='file' class='form-control' name='$name" . ($multiple ? "[]'" : "'") . " id='$id_input' $accept $multiple $required $disabled>";

    } else {
      // Inputs normales con validaciones opcionales
      $extra_attrs = '';

      if (isset($campo['subtype'])) {
        switch ($campo['subtype']) {
          case 'telefono':
            $extra_attrs = "pattern='\\d{10}' maxlength='10' minlength='10' inputmode='numeric' oninput=\"this.value = this.value.replace(/[^0-9]/g, '')\"";
            break;
          case 'curp':
            $extra_attrs = "pattern='[A-Z]{4}\\d{6}[A-Z]{6}\\d{2}' maxlength='18' minlength='18' style='text-transform:uppercase'";
            break;
          case 'rfc':
            $extra_attrs = "pattern='[A-ZÑ&]{3,4}\\d{6}[A-Z0-9]{3}' maxlength='13' minlength='12' style='text-transform:uppercase'";
            break;
          case 'email':
            $type = 'email';
            $extra_attrs = " ";
            break;
          case 'solo_letras':
            $extra_attrs = "pattern='[A-Za-zÁÉÍÓÚÑáéíóúñ\\s]+' oninput=\"this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑáéíóúñ\\s]/g, '')\"";
            break;
          case 'solo_numeros':
            $extra_attrs = "pattern='\\d+' inputmode='numeric' oninput=\"this.value = this.value.replace(/[^0-9]/g, '')\"";
            break;
          case 'cp':
            $extra_attrs = "pattern='\\d{5}' maxlength='5' minlength='5' inputmode='numeric'";
            break;
        }
      }

      echo "<input type='$type' class='form-control' name='$name' id='$id_input' value='$value' $placeholder $extra_attrs $required $disabled>";
    }

    echo "</div>"; // col
  }

  echo "</div>"; // row
  echo "</fieldset>";
}