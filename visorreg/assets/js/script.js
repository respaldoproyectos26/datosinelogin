// Debounce simple
function debounce(fn, delay=400) {
  let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

$(function(){
  const table = $('#tablaserverside').DataTable({
    processing: true,
    serverSide: true,
    serverMethod: 'POST',
    ajax: {
      url: 'server.php',
      type: 'POST',
      error: function(xhr){ console.error(xhr.responseText); }
    },
    // Define el mapeo de columnas con nombres exactos devueltos por PHP
    columns: [
      { data: 'id_contador', searchable:true },
      { data: 'usuario', searchable:true },
      { data: 'role_id', searchable:true },
      { data: 'role_name', searchable:true },
      { data: 'role_label', searchable:true },
      { data: 'permiso_id', searchable:true },
      { data: 'permiso_name', searchable:true },
      { data: 'permiso_label', searchable:true },
    ],
    columnDefs: [
      { targets: [4,6], className: 'text-right' },
      { targets: [1], className: 'text-left' },
      { targets: [0,2,5], className: 'text-center' },
      // Ejemplo de render numérico amigable
      { targets: [0,2,5], render: (data)=> data ? Number(data).toLocaleString('es-MX') : '' },
      // Evita ordenar si no quieres:
      // { targets: 2, orderable: false }
    ],
    order: [[0,'asc']],
    responsive: true,
    fixedHeader: true,
    deferRender: true,
    stateSave: true,   // recuerda filtros/orden/página
    orderMulti: true,
    lengthMenu: [[10,25,50,100,-1],[10,25,50,100,'Todos']],
    pageLength: 15,
    dom:
      "<'dt-top d-flex justify-content-between align-items-center'Bf>" + // Botones y caja de búsqueda global
      "tr" +
      "<'dt-bottom d-flex justify-content-between align-items-center'lip>",
    buttons: [
      { extend: 'copyHtml5',  text: 'Copiar' },
      { extend: 'csvHtml5',   text: 'CSV' },
      { extend: 'excelHtml5', text: 'Excel' },
      { extend: 'print',      text: 'Imprimir' },
      // ColVis (si usas Buttons ColVis): { extend:'colvis', text:'Columnas' }
    ],
    language: {
      url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-MX.json'
    },
    initComplete: function(){
      // Construye inputs por columna en la barra plegable
      const api = this.api();
      const container = $('#filtersContainer').empty();

      api.columns().every(function(colIdx){
        const col = this;
        const title = $(col.header()).text();
        const input = $(`<input type="text" placeholder="${title}" id="filter_${title}"" data-col="${colIdx}">`);
        // Restaura valor de stateSave si existe
        const state = api.state.loaded();
        const prev = state?.columns?.[colIdx]?.search?.search || '';
        if (prev) input.val(prev);

        input.on('keyup change', debounce(function(){
          col.search(this.value).draw();
        }, 400));

        container.append(input);
      });
    }
  });

  // Botón para plegar/desplegar barra
  $('#toggleFilters').on('click', function(){
    $('#filtersBar').slideToggle(150);
  });
});