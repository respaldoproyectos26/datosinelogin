let chartCursos = null;

function buildParams() {
  const curso = document.getElementById('filtroCurso').value || '';
  const inicio = document.getElementById('fechaInicio').value || '';
  const fin = document.getElementById('fechaFin').value || '';
  const q = new URLSearchParams({ curso, inicio, fin });
  return q.toString();
}

async function cargarResumen() {
  const params = buildParams();
  const res = await fetch(`api/get_resumen.php?${params}`);
  const json = await res.json();

  // KPIs
  const total = Number(json?.kpi?.total || 0);
  const si = Number(json?.kpi?.asistieron || 0);
  const no = Number(json?.kpi?.no_asistieron || 0);
  const prom = Number(json?.kpi?.promedio_eval || 0);

  document.getElementById('kpiTotal').textContent = total.toLocaleString('es-MX');
  document.getElementById('kpiAsistieron').textContent = si.toLocaleString('es-MX');
  document.getElementById('kpiNo').textContent = no.toLocaleString('es-MX');
  document.getElementById('kpiEval').textContent = isNaN(prom) ? '0.0' : prom.toFixed(2);

  const pctSi = total ? Math.round((si/total)*100) : 0;
  const pctNo = total ? Math.round((no/total)*100) : 0;
  document.getElementById('kpiAsistieronPct').textContent = pctSi + '%';
  document.getElementById('kpiNoPct').textContent = pctNo + '%';

  // Chart
  const labels = (json.cursos || []).map(r => r.curso || '—');
  const dataSi = (json.cursos || []).map(r => Number(r.asistieron || 0));
  const dataNo = (json.cursos || []).map(r => Number(r.no_asistieron || 0));

  const ctx = document.getElementById('chartCursos').getContext('2d');
  if (chartCursos) { chartCursos.destroy(); }
  chartCursos = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        { label: 'Asistieron', data: dataSi, borderWidth: 1 },
        { label: 'No asistieron', data: dataNo, borderWidth: 1 }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: true } },
      plugins: {
        tooltip: { mode: 'index', intersect: false },
        legend: { position: 'top' }
      }
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const tabla = $('#tablaAsistencias').DataTable({
    ajax: { url: 'api/get_asistencia.php', dataSrc: 'data' },
    columns: [
      { data: 'id' },
      { data: 'nombre' },
      { data: 'curso' },
      { data: 'fecha' },
      { data: 'hora_entrada' },
      { data: 'hora_salida' },
      { data: 'asistencia' },
      { data: 'evaluacion' },
      { data: 'observaciones' },
      { data: 'registrado_por' }
    ],
    order: [[0, 'desc']],
    responsive: true,
    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
  });

  const recargarTodo = () => {
    const params = buildParams();
    tabla.ajax.url(`api/get_asistencia.php?${params}`).load();
    cargarResumen();
  };

  document.getElementById('btnBuscar').addEventListener('click', recargarTodo);
  document.getElementById('btnExportarExcel').addEventListener('click', () => {
    window.open(`api/export_excel.php?${buildParams()}`, '_blank');
  });
  document.getElementById('btnExportarPDF').addEventListener('click', () => {
    window.open(`api/export_pdf.php?${buildParams()}`, '_blank');
  });

  // primera carga
  cargarResumen();
});
