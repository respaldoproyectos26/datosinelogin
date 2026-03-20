// filtrodatos2/js/script.js
let tabla = null;

$(function () {
  console.log("jQuery cargado correctamente");

  const $loader = $('#loader');

  // Escape mínimo para HTML
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
  }[m]));

  // Cargar opciones en los filtros (DISTINCT)
  function cargarOpciones(filtro, selector) {
    return $.getJSON(`ajax/get_filtros.php?filtro=${encodeURIComponent(filtro)}`)
      .done(function (data) {
        if (data && data.error) {
          console.error(`❌ Error filtro ${filtro}:`, data.error);
          $(selector).html('<option value="">Error</option>');
          return;
        }
        const opts = ['<option value="">Todos</option>'];
        (data || []).forEach(v => opts.push(`<option value="${esc(v)}">${esc(v)}</option>`));
        $(selector).html(opts.join(''));
      })
      .fail(function (xhr) {
        console.error(`❌ Error al cargar filtro ${filtro}:`, xhr.responseText);
        $(selector).html('<option value="">Error</option>');
      });
  }

  // Cargar filtros (paralelo) y luego inicializar tabla
  $.when(
    cargarOpciones('nombre',  '#filtro_nombre'),
    cargarOpciones('paterno', '#filtro_paterno'),
    cargarOpciones('materno', '#filtro_materno'),
    cargarOpciones('estado',  '#filtro_estado'),
    cargarOpciones('municalc','#filtro_municalc'),
    cargarOpciones('seccion', '#filtro_seccion')
  ).always(initTabla);

  // Botón DataTables "Crear"
  $.fn.dataTable.ext.buttons.crear = {
    text: '<i class="bi bi-plus-circle"></i> Crear registro',
    className: 'btn btn-success',
    action: () => {
      const modalEl = document.getElementById('modalCrear');
      const formEl  = document.getElementById('formCrear');

      if (modalEl && formEl) {
        formEl.reset();
        new bootstrap.Modal(modalEl).show();
      } else {
        window.location.href = '../formulario2/index.php';
      }
    }
  };

  $.fn.dataTable.ext.buttons.exportar = {
    text: '<i class="bi bi-plus-circle"></i> Exportar registros',
    className: 'btn btn-primary',
    action: () => {
      const modalEl2 = document.getElementById('modalExportar');

      if (modalEl2) {
        new bootstrap.Modal(modalEl2).show();
      } else {
        window.location.href = '../exportartablas/';
      }
    }
  };

  function initTabla() {
    if ($.fn.DataTable.isDataTable("#productsTable")) {
      $('#productsTable').DataTable().destroy();
      $('#productsTable tbody').empty();
    }

    tabla = $('#productsTable').DataTable({
      processing: true,
      serverSide: false,
      ajax: {
        url: "ajax/get_datos.php",
        type: "POST",
        data: function (d) {
          d.nombre   = $('#filtro_nombre').val()  || '';
          d.paterno  = $('#filtro_paterno').val() || '';
          d.materno  = $('#filtro_materno').val() || '';
          d.estado   = $('#filtro_estado').val()  || '';
          d.municalc = $('#filtro_municalc').val()|| '';
          d.seccion  = $('#filtro_seccion').val() || '';
        },
        dataSrc: function (json) {
          if (json && json.error) {
            console.error("❌ Error en get_datos.php:", json.error);
            return [];
          }
          return (json && json.data) ? json.data : [];
        },
        error: function (xhr, status, error) {
          console.error("❌ Error en AJAX DataTable:", status, error);
          console.log("Respuesta completa:", xhr.responseText);
        }
      },
      scrollX: true,
      autoWidth: false,
      responsive: false,
      dom: 'lBfrtip',
      buttons: [
        'crear',
        // {
        //   text: '<i class="bi bi-box-arrow-up"></i> Exportar registros',
        //   className: 'btn btn-dark',
        //   action: () => {
        //     const modalEl2 = document.getElementById('modalExportar');
        //     if (modalEl2) {
        //       new bootstrap.Modal(modalEl2).show();
        //     } else {
        //       window.location.href = '../exportartablas/';
        //     }
        //   }
        // }
      ],
      language: {
        url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
      },

      columns: [
        { data: "id" },
        { data: "nombre" },
        { data: "paterno" },
        { data: "materno" },
        { data: "estado" },
        { data: "municalc" },
        { data: "seccion" },
        {
          data: "foto",
          render: function (data) {
            if (!data) return '<span class="text-muted">Sin foto</span>';

            // 1) Normaliza el path guardado en BD
            let p = String(data).trim().replace(/\\/g, '/');

            // 2) Quita prefijos que a veces vienen duplicados
            p = p.replace(/^\/+/, '');          // "/2026/01/..." -> "2026/01/..."
            p = p.replace(/^uploads\//, '');    // "uploads/2026/01/..." -> "2026/01/..."

            // 3) Base ABSOLUTA a uploads (robusto aunque la vista esté en subcarpetas o haya <base>)
            const uploadsBase = new URL('../uploads/', window.location.href).href;

            // 4) URL final
            const src = uploadsBase + p;

            return `<img src="${esc(encodeURI(src))}" alt="Foto" class="img-thumbnail" style="width:50px;height:50px;">`;
          },
          orderable: false
        },
        {
          data: null,
          render: function (data, type, row) {
            return `
              <button class="btn btn-sm btn-success editar-btn" data-id="${esc(row.id)}">
                <i class="bi bi-pencil-square"></i>
              </button>
              <button class="btn btn-sm btn-danger eliminar-btn" data-id="${esc(row.id)}">
                <i class="bi bi-trash"></i>
              </button>
            `;
          },
          orderable: false,
          searchable: false
        }
      ],

      columnDefs: [
        { targets: [0, 6, 7, 8], className: "text-center fuente-personalizada" },
        { targets: "_all", className: "fuente-personalizada" }
      ]
    });

    // Loader
    tabla.on('preXhr.dt', () => $loader.show());
    tabla.on('xhr.dt', () => $loader.hide());

    // Recargar al cambiar filtros
    $('#filtro_nombre, #filtro_paterno, #filtro_materno, #filtro_estado, #filtro_municalc, #filtro_seccion')
      .on('change', () => tabla.ajax.reload(null, false));
  }

  // Editar
  $('#productsTable').on('click', '.editar-btn', function () {
    const id = $(this).data('id');
    window.location.href = `../formulario2/index.php?id=${encodeURIComponent(id)}`;
  });

  // Eliminar
  $('#productsTable').on('click', '.eliminar-btn', function () {
    const id = $(this).data('id');

    Swal.fire({
      icon: 'warning',
      title: '¿Estás seguro?',
      text: 'Esta acción no se puede deshacer',
      showCancelButton: true,
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: 'ajax/delete.php',
        method: 'POST',
        dataType: 'json',
        data: { id }
      })
      .done(function (r) {
        if (r && r.ok) {
          Swal.fire('Eliminado', '', 'success');
          if (tabla) tabla.ajax.reload(null, false);
        } else {
          Swal.fire('Error', (r && r.msg) || 'No se pudo eliminar', 'error');
        }
      })
      .fail(function (xhr) {
        console.error(xhr.responseText);
        Swal.fire('Error', 'Error en la petición', 'error');
      });
    });
  });

  // Cascada Estado → Municipio → Sección (solo si existen en esta vista)
  const $edo = $('#idedo');
  const $mun = $('#idmunicalc');
  const $sec = $('#seccion');

  if ($edo.length && $mun.length && $sec.length) {
    $edo.on('change', function () {
      const idedo = this.value;

      $mun.html('<option value="">Cargando...</option>');
      $sec.html('<option value="">Selecciona un municipio primero</option>');

      // OJO: aquí SIEMPRE con ajax/
      $.post('ajax/get_municipios.php', { idedo }, function (resp) {
        const opts = ['<option value="">Selecciona un municipio</option>'];
        (resp || []).forEach(m => opts.push(`<option value="${esc(m.municalc)}">${esc(m.municalc)}</option>`));
        $mun.html(opts.join(''));
      }, 'json').fail(function (xhr) {
        console.error(xhr.responseText);
        $mun.html('<option value="">Error al cargar</option>');
      });
    });

    $mun.on('change', function () {
      const idmunicalc = this.value;

      $sec.html('<option value="">Cargando...</option>');

      $.post('ajax/get_secciones.php', { idmunicalc }, function (resp) {
        const opts = ['<option value="">Selecciona una sección</option>'];
        (resp || []).forEach(s => opts.push(`<option value="${esc(s.seccion)}">${esc(s.seccion)}</option>`));
        $sec.html(opts.join(''));
      }, 'json').fail(function (xhr) {
        console.error(xhr.responseText);
        $sec.html('<option value="">Error al cargar</option>');
      });
    });
  }
});
