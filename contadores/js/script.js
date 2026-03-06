document.addEventListener('DOMContentLoaded', () => {
  // Orden y títulos de las tarjetas:
  const items = [
    { key: 'total',     title: 'Total' },
    { key: 'rg',        title: 'RG' },
    { key: 'rc',        title: 'RC' },
    { key: 'prom',      title: 'Promovidos' },
    { key: 'secciones', title: 'Secciones únicas' },
    { key: 'municipios',title: 'Municipios únicos' },
    { key: 'estados',   title: 'Estados únicos' },
  ];

  // Paleta rotativa (Bootstrap). No se repiten hasta agotar el arreglo.
  const COLORS = [
    'text-bg-primary',
    'text-bg-success',
    'text-bg-danger',
    'text-bg-warning',
    'text-bg-info',
    'text-bg-secondary',
  ];

  const $container = document.getElementById('cardsContainer');

  fetch('ajax/get_contadores.php', { method: 'POST' })
    .then(r => r.json())
    .then(res => {
      if (!res.ok) throw new Error(res.msg || 'Error en contadores');

      // Construye las cards con color rotativo
      const html = items.map((it, i) => {
        const color = COLORS[i % COLORS.length];
        const value = (res[it.key] ?? 0).toLocaleString('es-MX');
        return `
          <div class="card ${color} shadow text-center" style="width: 16rem;">
            <div class="card-body">
              <h5 class="card-title">${it.title}</h5>
              <p class="card-text display-6">${value}</p>
            </div>
          </div>
        `;
      }).join('');

      $container.innerHTML = html;
    })
    .catch(err => {
      console.error(err);
      if (window.Swal) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar los contadores' });
      } else {
        $container.innerHTML = `<div class="alert alert-danger w-100">No se pudieron cargar los contadores.</div>`;
      }
    });
});
