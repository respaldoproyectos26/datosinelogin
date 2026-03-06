document.addEventListener('DOMContentLoaded', () => {
  let chartInstance = null;
  const ctx = document.getElementById('graficoResumen').getContext('2d');

  const cargarGrafico = (tipo = 'bar') => {
    fetch('api/resumen_api.php')
      .then(r => r.json())
      .then(data => {
        const etiquetas = ['Total', 'CURPs válidos', 'CURPs inválidos'];
        const valores = [data.total, data.curps_validos, data.curps_invalidos];

        // Mostrar botón de pastel solo si hay datos no cero
        const btnPastel = document.getElementById('btnPastel');
        if (valores.some(v => v > 0)) {
        btnPastel.style.display = 'inline-block';
        } else {
        btnPastel.style.display = 'none';
        }

        const tipoChart = (tipo === 'lineas') ? 'line' : (tipo === 'pastel' ? 'pie' : 'bar');

        const config = {
            type: tipoChart,
            data: {
                labels: etiquetas,
                datasets: [{
                label: 'Registros',
                data: valores,
                backgroundColor: ['#0d6efd', '#198754', '#dc3545'],
                borderColor: ['#0a58ca', '#146c43', '#bb2d3b'],
                borderWidth: 2,
                fill: false,
                tension: 0.1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                legend: {
                    display: tipoChart !== 'bar',
                    labels: {
                    color: '#fff'  // 👈 texto de la leyenda en blanco
                    }
                },
                title: {
                    display: true,
                    text: 'Resumen de CURPs',
                    color: '#fff'    // 👈 título en blanco
                }
                },
                scales: tipoChart === 'pie' ? {} : {
                x: {
                    ticks: { color: '#fff' },  // 👈 ejes en blanco
                    grid: { color: 'rgba(255,255,255,0.1)' }
                },
                y: {
                    ticks: { color: '#fff' },
                    grid: { color: 'rgba(255,255,255,0.1)' }
                }
                }
            }
        };

        if (chartInstance) chartInstance.destroy(); // 👈 IMPORTANTE

        chartInstance = new Chart(ctx, config);
      })
      .catch(err => {
        console.error('Error al cargar gráfico:', err);
        if (typeof Swal !== 'undefined') {
          Swal.fire('Error', 'No se pudo cargar el gráfico.', 'error');
        } else {
          alert('Error al cargar gráfico.');
        }
      });
  };

  cargarGrafico(); // carga inicial como barras

  document.querySelectorAll('button[data-chart]').forEach(btn => {
    btn.addEventListener('click', () => {
      const tipo = btn.dataset.chart;
      cargarGrafico(tipo);
    });
  });
});
