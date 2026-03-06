$(document).ready(function() {
    console.log("jQuery cargado correctamente");

    // Cargar opciones en los filtros
    function cargarOpciones(filtro, elemento) {
        $.getJSON('ajax/get_filtros.php?filtro=' + filtro, function(data) {
            console.log("🔹 Datos recibidos para el filtro", filtro, ":", data);
            let opciones = '<option value="">Toda(o)s</option>';
            $.each(data, function(index, value) {
                opciones += `<option value="${value}">${value}</option>`;
            });
            $(elemento).html(opciones);
        }).fail(function(xhr) {
            console.error("❌ Error al cargar filtros:", xhr.responseText);
        });
    }
    cargarOpciones('nombre', '#filtro_nombre');
    cargarOpciones('paterno', '#filtro_paterno');
    cargarOpciones('materno', '#filtro_materno');
    cargarOpciones('estado', '#filtro_estado');
    cargarOpciones('municalc', '#filtro_municalc');
    cargarOpciones('seccion', '#filtro_seccion');
    // Destruir DataTable si ya existe
    if ($.fn.DataTable.isDataTable("#productsTable")) {
        $('#productsTable').DataTable().destroy();
    }
    $('#productsTable tbody').empty(); // Limpia el cuerpo de la tabla

    // Extensión de botón "Crear"
    $.fn.dataTable.ext.buttons.crearRegistro = {
    text: '<i class="bi bi-plus-circle"></i> Crear registro',
    className: 'btn btn-success',
    action: function () {
        // limpia el form
        document.getElementById('formCrear').reset();
        // abre modal
        new bootstrap.Modal(document.getElementById('modalCrear')).show();
    }
    };

    $.fn.dataTable.ext.buttons.crear = {
        text: '<i class="bi bi-plus-circle"></i> Crear registro',
        className: 'btn btn-success',
        action: ()=> window.location.href = '../formEstatal/'
    };

    // Inicializar DataTable
    const tabla = $('#productsTable').DataTable({
        "processing": true,
        "serverSide": false,
        "ajax": {
            "url": "ajax/get_datos.php",
            "type": "POST",
            "data": function(d) {
                d.nombre = $('#filtro_nombre').val();
                d.paterno = $('#filtro_paterno').val();
                d.materno = $('#filtro_materno').val();
                d.estado = $('#filtro_estado').val();
                d.municalc = $('#filtro_municalc').val();
                d.seccion = $('#filtro_seccion').val();
                console.log("Filtros enviados a PHP:", d); // <-- Debug en consola
            },
            "dataSrc": "data",
            "error": function(xhr, status, error) {
                console.error("Error en AJAX:", status, error);
                console.log("Respuesta completa:", xhr.responseText);
            }
        },
        scrollX: true,
        autoWidth: false,
        responsive: false, // si usas scrollX mejor dejarlo en false
        dom: 'lBfrtip',
        buttons: ['crear'],
        language: {
            "processing": "Procesando...",
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "emptyTable": "Ningún dato disponible en esta tabla",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "search": "Buscar:",
            "infoThousands": ",",
            "loadingRecords": "Cargando...",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "aria": {
                "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                "sortDescending": ": Activar para ordenar la columna de manera descendente"
            },
            "buttons": {
                "copy": "Copiar",
                "colvis": "Visibilidad",
                "collection": "Colección",
                "colvisRestore": "Restaurar visibilidad",
                "copyKeys": "Presione ctrl o u2318 + C para copiar los datos de la tabla al portapapeles del sistema. <br \/> <br \/> Para cancelar, haga clic en este mensaje o presione escape.",
                "copySuccess": {
                    "1": "Copiada 1 fila al portapapeles",
                    "_": "Copiadas %ds fila al portapapeles"
                },
                "copyTitle": "Copiar al portapapeles",
                "csv": "CSV",
                "excel": "Excel",
                "pageLength": {
                    "-1": "Mostrar todas las filas",
                    "_": "Mostrar %d filas"
                },
                "pdf": "PDF",
                "print": "Imprimir",
                "renameState": "Cambiar nombre",
                "updateState": "Actualizar",
                "createState": "Crear Estado",
                "removeAllStates": "Remover Estados",
                "removeState": "Remover",
                "savedStates": "Estados Guardados",
                "stateRestore": "Estado %d"
            },
            "autoFill": {
                "cancel": "Cancelar",
                "fill": "Rellene todas las celdas con <i>%d<\/i>",
                "fillHorizontal": "Rellenar celdas horizontalmente",
                "fillVertical": "Rellenar celdas verticalmentemente"
            },
            "decimal": ",",
            "searchBuilder": {
                "add": "Añadir condición",
                "button": {
                    "0": "Constructor de búsqueda",
                    "_": "Constructor de búsqueda (%d)"
                },
                "clearAll": "Borrar todo",
                "condition": "Condición",
                "conditions": {
                    "date": {
                        "after": "Despues",
                        "before": "Antes",
                        "between": "Entre",
                        "empty": "Vacío",
                        "equals": "Igual a",
                        "notBetween": "No entre",
                        "notEmpty": "No Vacio",
                        "not": "Diferente de"
                    },
                    "number": {
                        "between": "Entre",
                        "empty": "Vacio",
                        "equals": "Igual a",
                        "gt": "Mayor a",
                        "gte": "Mayor o igual a",
                        "lt": "Menor que",
                        "lte": "Menor o igual que",
                        "notBetween": "No entre",
                        "notEmpty": "No vacío",
                        "not": "Diferente de"
                    },
                    "string": {
                        "contains": "Contiene",
                        "empty": "Vacío",
                        "endsWith": "Termina en",
                        "equals": "Igual a",
                        "notEmpty": "No Vacio",
                        "startsWith": "Empieza con",
                        "not": "Diferente de",
                        "notContains": "No Contiene",
                        "notStarts": "No empieza con",
                        "notEnds": "No termina con"
                    },
                    "array": {
                        "not": "Diferente de",
                        "equals": "Igual",
                        "empty": "Vacío",
                        "contains": "Contiene",
                        "notEmpty": "No Vacío",
                        "without": "Sin"
                    }
                },
                "data": "Data",
                "deleteTitle": "Eliminar regla de filtrado",
                "leftTitle": "Criterios anulados",
                "logicAnd": "Y",
                "logicOr": "O",
                "rightTitle": "Criterios de sangría",
                "title": {
                    "0": "Constructor de búsqueda",
                    "_": "Constructor de búsqueda (%d)"
                },
                "value": "Valor"
            },
            "searchPanes": {
                "clearMessage": "Borrar todo",
                "collapse": {
                    "0": "Paneles de búsqueda",
                    "_": "Paneles de búsqueda (%d)"
                },
                "count": "{total}",
                "countFiltered": "{shown} ({total})",
                "emptyPanes": "Sin paneles de búsqueda",
                "loadMessage": "Cargando paneles de búsqueda",
                "title": "Filtros Activos - %d",
                "showMessage": "Mostrar Todo",
                "collapseMessage": "Colapsar Todo"
            },
            "select": {
                "cells": {
                    "1": "1 celda seleccionada",
                    "_": "%d celdas seleccionadas"
                },
                "columns": {
                    "1": "1 columna seleccionada",
                    "_": "%d columnas seleccionadas"
                },
                "rows": {
                    "1": "1 fila seleccionada",
                    "_": "%d filas seleccionadas"
                }
            },
            "thousands": ".",
            "datetime": {
                "previous": "Anterior",
                "next": "Proximo",
                "hours": "Horas",
                "minutes": "Minutos",
                "seconds": "Segundos",
                "unknown": "-",
                "amPm": [
                    "AM",
                    "PM"
                ],
                "months": {
                    "0": "Enero",
                    "1": "Febrero",
                    "10": "Noviembre",
                    "11": "Diciembre",
                    "2": "Marzo",
                    "3": "Abril",
                    "4": "Mayo",
                    "5": "Junio",
                    "6": "Julio",
                    "7": "Agosto",
                    "8": "Septiembre",
                    "9": "Octubre"
                },
                "weekdays": [
                    "Dom",
                    "Lun",
                    "Mar",
                    "Mie",
                    "Jue",
                    "Vie",
                    "Sab"
                ]
            },
            "editor": {
                "close": "Cerrar",
                "create": {
                    "button": "Nuevo",
                    "title": "Crear Nuevo Registro",
                    "submit": "Crear"
                },
                "edit": {
                    "button": "Editar",
                    "title": "Editar Registro",
                    "submit": "Actualizar"
                },
                "remove": {
                    "button": "Eliminar",
                    "title": "Eliminar Registro",
                    "submit": "Eliminar",
                    "confirm": {
                        "_": "¿Está seguro que desea eliminar %d filas?",
                        "1": "¿Está seguro que desea eliminar 1 fila?"
                    }
                },
                "error": {
                    "system": "Ha ocurrido un error en el sistema (<a target=\"\\\" rel=\"\\ nofollow\" href=\"\\\">Más información&lt;\\\/a&gt;).<\/a>"
                },
                "multi": {
                    "title": "Múltiples Valores",
                    "info": "Los elementos seleccionados contienen diferentes valores para este registro. Para editar y establecer todos los elementos de este registro con el mismo valor, hacer click o tap aquí, de lo contrario conservarán sus valores individuales.",
                    "restore": "Deshacer Cambios",
                    "noMulti": "Este registro puede ser editado individualmente, pero no como parte de un grupo."
                }
            },
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "stateRestore": {
                "creationModal": {
                    "button": "Crear",
                    "name": "Nombre:",
                    "order": "Clasificación",
                    "paging": "Paginación",
                    "search": "Busqueda",
                    "select": "Seleccionar",
                    "columns": {
                        "search": "Búsqueda de Columna",
                        "visible": "Visibilidad de Columna"
                    },
                    "title": "Crear Nuevo Estado",
                    "toggleLabel": "Incluir:"
                },
                "emptyError": "El nombre no puede estar vacio",
                "removeConfirm": "¿Seguro que quiere eliminar este %s?",
                "removeError": "Error al eliminar el registro",
                "removeJoiner": "y",
                "removeSubmit": "Eliminar",
                "renameButton": "Cambiar Nombre",
                "renameLabel": "Nuevo nombre para %s",
                "duplicateError": "Ya existe un Estado con este nombre.",
                "emptyStates": "No hay Estados guardados",
                "removeTitle": "Remover Estado",
                "renameTitle": "Cambiar Nombre Estado"
            }},
            columns: [
                { "data": "id" },
                { "data": "estado" },
                { "data": "figura" },
                { "data": "nombre" },
                { "data": "clave_elector" },
                { "data": "cel" },
                { "data": "seccion" },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                    return `<button class="btn btn-sm btn-warning">Editar</button>`;
                    }
                }
            ],
        });
        tabla.on('preXhr.dt', function() {
            $('#loader').show(); // muestra loader
        });
        tabla.on('xhr.dt', function() {
            $('#loader').hide(); // oculta loader
        });
        // Recargar tabla cuando cambian los filtros
        $('#filtro_nombre, #filtro_paterno, #filtro_materno, #filtro_estado, #filtro_municalc, #filtro_seccion')
        .on('change', function() {
            tabla.ajax.reload(null, false);  // 🚀 Recargar sin perder paginación
        });
    });

    // Validar sin acentos
function validarSinAcentos(el){
  el.value = el.value.replace(/[áéíóúÁÉÍÓÚ]/g, '');
}

// Eventos de cascada (estados -> municipios -> secciones)
$('#idedo').on('change', function(){
  const idedo = this.value;
  $('#idmunicalc').html('<option value="">Cargando...</option>');
  $.post('get_municipios.php', { idedo }, function(resp){
    const opts = ['<option value="">Selecciona un municipio</option>'];
    (resp || []).forEach(m => opts.push(`<option value="${m.municalc}">${m.municalc}</option>`));
    $('#idmunicalc').html(opts.join(''));
  }, 'json');
});

$('#idmunicalc').on('change', function(){
  const idmunicalc = this.value;
  $('#seccion').html('<option value="">Cargando...</option>');
  $.post('get_secciones.php', { idmunicalc }, function(resp){
    const opts = ['<option value="">Selecciona una sección</option>'];
    (resp || []).forEach(s => opts.push(`<option value="${s.seccion}">${s.seccion}</option>`));
    $('#seccion').html(opts.join(''));
  }, 'json');
});

// Submit del form (AJAX)
$('#formCrear').on('submit', function(e){
  e.preventDefault();

  const formData = $(this).serialize(); // por ahora; si luego hay archivos usaremos FormData
  $.ajax({
    url: '../../formEstatal/get_datos.php',   // <— tu endpoint actual (lo ajustamos cuando me lo pases)
    type: 'POST',
    data: formData,
    dataType: 'json',
    success: function(r){
      if (r && r.ok){
        Swal.fire({ icon:'success', title:'Guardado', timer:1200, showConfirmButton:false });
        $('#modalCrear').modal('hide');
        tabla.ajax.reload(null, false);
      }else{
        Swal.fire({ icon:'warning', title:'Revisa', text: (r && r.msg) || 'No se pudo guardar' });
      }
    },
    error: function(xhr){
      Swal.fire({ icon:'error', title:'Error', text:'No se pudo guardar' });
      console.error(xhr.responseText);
    }
  });
});

// ===== Helpers =====
  const rmAccents = s => s.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  const toUpperNoAccents = el => el.value = rmAccents(el.value).toUpperCase();
  document.querySelectorAll('.text-uppercase').forEach(el=>{
    el.addEventListener('input', ()=> toUpperNoAccents(el));
  });

  // Solo dígitos
  ['cel','telpart','cp','tel_proponente'].forEach(id=>{
    const el = document.getElementById(id);
    if(el){ el.addEventListener('input', ()=> el.value = el.value.replace(/\D+/g,'').slice(0, el.id==='cp'?5:10)); }
  });

  // Cascada Estado → Municipio → Sección
const $estado = document.getElementById('estado');
const $muni   = document.getElementById('municalc');
const $seccion= document.getElementById('seccion');

if ($estado && $muni && $seccion) {
  $estado.addEventListener('change', ()=> {
    const v = $estado.value;
    $muni.innerHTML = '<option value="">Cargando...</option>';
    fetch('get_municipios.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'idedo='+encodeURIComponent(v)
    })
    .then(r=>r.json()).then(data=>{
      $muni.innerHTML = '<option value="">Selecciona un municipio</option>';
      (data||[]).forEach(m=> $muni.insertAdjacentHTML('beforeend', `<option value="${m.municalc}">${m.municalc}</option>`));
      $seccion.innerHTML = '<option value="">Selecciona un municipio primero</option>';
    }).catch(()=>{
      $muni.innerHTML='<option value="">Error al cargar</option>';
    });
  });

  $muni.addEventListener('change', ()=> {
    const v = $muni.value;
    $seccion.innerHTML = '<option value="">Cargando...</option>';
    fetch('get_secciones.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'idmunicalc='+encodeURIComponent(v)
    })
    .then(r=>r.json()).then(data=>{
      $seccion.innerHTML = '<option value="">Selecciona una sección</option>';
      (data||[]).forEach(s=> $seccion.insertAdjacentHTML('beforeend', `<option value="${s.seccion}">${s.seccion}</option>`));
    }).catch(()=>{
      $seccion.innerHTML='<option value="">Error al cargar</option>';
    });
  });

  tabla.on('preXhr.dt', ()=> $('#loader').show());
  tabla.on('xhr.dt',    ()=> $('#loader').hide());
}