const pageSize = 20;

const AG_GRID_LOCALE_ES = {
  // Puedes extender con más traducciones si gustas
  page: "Página",
  more: "más",
  to: "a",
  of: "de",
  next: "Siguiente",
  last: "Último",
  first: "Primero",
  previous: "Anterior",
  loadingOoo: "Cargando...",
  noRowsToShow: "No hay registros",
  searchOoo: "Buscar...",
  equals: "Igual",
  notEqual: "Distinto",
  filterOoo: "Filtrar...",
  applyFilter: "Aplicar",
  clearFilter: "Limpiar",
  sortAscending: "Orden ascendente",
  sortDescending: "Orden descendente",
  // Set Filter
  selectAll: "Seleccionar todo",
  searchOoo: "Buscar...",
  blanks: "Vacíos",
  // Filter Conditions
  equals: "Igual a",
  notEqual: "Distinto de",
  lessThan: "Menor que",
  greaterThan: "Mayor que",
  contains: "Contiene",
  notContains: "No contiene",
  // Pagination
  pageSize: "Tamaño de página",
};

const gridOptions = {
  columnDefs: [
    { headerName: "ID", field: "id", width: 80 },
    { headerName: "Nombre", field: "nombre_completo", width: 200 },
    { headerName: "CURP", field: "curp", width: 180 },
    { headerName: "Clave Elector", field: "clave_elector", width: 180 },
    { headerName: "Sección", field: "seccion", width: 100 },
    { headerName: "Vigencia", field: "vigencia", width: 100 },
    { headerName: "Emisión", field: "emision", width: 100 },
    { headerName: "Fecha", field: "created_at", width: 160 },
    {
      headerName: "Foto",
      field: "img_path",
      width: 120,
      cellRenderer: (params) => {
        return params.value ? `<img src="${params.value}" style="height:40px">` : 'Sin imagen';
      }
    },
    {
      headerName: "Acciones",
      field: "id",
      width: 160,
      cellRenderer: (params) => {
        const id = params.value;
        let html = '';
        if (window.canEditar) html += `<button class="btn btn-sm btn-warning" data-edit="${id}">Editar</button>`;
        if (window.canEliminar) html += ` <button class="btn btn-sm btn-danger" data-del="${id}">Eliminar</button>`;
        return html;
      }
    },
  ],
  localeText: AG_GRID_LOCALE_ES,
  defaultColDef: {
    resizable: true,
    filter: true,
    sortable: true,
  },
  rowModelType: 'infinite',
  //rowData: [], // ← Esto
  pagination: true,
  paginationPageSize: pageSize,
  cacheBlockSize: pageSize,
  /*
  statusBar: {
    statusPanels: [
      { statusPanel: 'agTotalRowCountComponent', align: 'left' },
      { statusPanel: 'agSelectedRowCountComponent' },
      { statusPanel: 'agAggregationComponent' }
    ]
  },
  */
  datasource: {
    getRows(params) {
      const startRow = params.startRow; // e.g. 0
      const page = startRow / pageSize + 1;

      // Orden (solo primer col para ejemplo)
      const sortModel = params.sortModel && params.sortModel[0] ? params.sortModel[0] : null;

      const payload = {
        page,
        size: pageSize,
        sortField: sortModel ? sortModel.colId : null,
        sortDir: sortModel ? sortModel.sort : null,
        filters: params.filterModel || {}
      };

      fetch('api/registros.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      })
        .then(r => r.json())
        .then(data => {
          params.successCallback(data.rows, data.total);
          actualizarConteo(data.total);  // 👈 ACTUALIZA EL CONTADOR
          const conteoDiv = document.getElementById('conteo');
          conteoDiv.dataset.total = data.total;
          conteoDiv.textContent = `Total de registros: ${data.total}`;
        })
        .catch(err => {
          console.error(err);
          params.failCallback();
        });
    }
  }
};

/*
document.addEventListener('DOMContentLoaded', function() {
  const eGridDiv = document.querySelector('#myGrid');
  new agGrid.Grid(eGridDiv, gridOptions);

  // Debug: fuerza render de columnas
  if (gridApi) {
    gridApi.setRowData([]); // fuerza dibujado aunque esté vacío
  }
});
*/
document.addEventListener('DOMContentLoaded', function () {
  const eGridDiv = document.querySelector('#myGrid');

  // 👉 Definir primero funciones auxiliares
  function actualizarConteo(total) {
    const conteoDiv = document.getElementById('conteo');
    conteoDiv.dataset.total = total;
    conteoDiv.textContent = `Total de registros: ${total}`;
  }

  function actualizarSeleccionados() {
    const totalSel = gridApi.getSelectedRows().length;
    const conteoDiv = document.getElementById('conteo');
    if (conteoDiv.dataset.total) {
      conteoDiv.textContent = `Total de registros: ${conteoDiv.dataset.total} | Seleccionados: ${totalSel}`;
    }
  }

  // 👉 Configurar selección múltiple y evento
  gridOptions.rowSelection = 'multiple';
  gridOptions.onSelectionChanged = actualizarSeleccionados;

  // 👉 Crear el grid
  const gridApi = agGrid.createGrid(eGridDiv, gridOptions);

  // 👉 Autoajustar columnas después de renderizar
  setTimeout(() => {
    const allColumnIds = [];
    gridOptions.columnApi.getAllColumns().forEach(col => {
      allColumnIds.push(col.getId());
    });
    gridOptions.columnApi.autoSizeColumns(allColumnIds);
  }, 500);

  document.getElementById('btnSelectAll').addEventListener('click', () => {
    gridApi.selectAll();
    actualizarSeleccionados(); // actualiza contador
  });

  document.getElementById('btnDeselectAll').addEventListener('click', () => {
    gridApi.deselectAll();
    actualizarSeleccionados();
  });

  document.getElementById('btnExportSelected').addEventListener('click', () => {
  const selectedRows = gridApi.getSelectedRows();
  if (selectedRows.length === 0) {
    return Swal.fire('Aviso', 'No hay filas seleccionadas', 'info');
  }

    // Exportar como CSV (solo columnas visibles)
    gridApi.exportDataAsCsv({
      onlySelected: true,
      fileName: 'seleccionados.csv'
    });
  });
});

function openModal(titulo) {
  document.getElementById('crudForm').reset();
  document.getElementById('modalTitle').textContent = titulo;
  const modal = new bootstrap.Modal(document.getElementById('crudModal'));
  modal.show();
}
