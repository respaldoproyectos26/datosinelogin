document.addEventListener('DOMContentLoaded', () => {
  fetch('api/resumen_api.php') 
    .then(response => response.json())
    .then(data => {
      console.log("Respuesta de la API:", data); // 👈 Aquí

      const container = document.getElementById('resumenCards');

      const cards = [
        {
          titulo: 'Total de registros',
          valor: data.total,
          clase: 'primary'
        },
        {
          titulo: 'CURPs válidos',
          valor: data.curps_validos,
          clase: 'success'
        },
        {
          titulo: 'CURPs inválidos',
          valor: data.curps_invalidos,
          clase: 'danger'
        },
        {
          titulo: 'Secciones únicas',
          valor: data.secciones,
          clase: 'warning'
        }
      ];

      container.innerHTML = cards.map(card => `
        <div class="card text-bg-${card.clase} shadow m-2" style="width: 16rem;">
            <div class="card-body text-center">
            <h5 class="card-title">${card.titulo}</h5>
            <h3 class="card-text">${card.valor.toLocaleString()}</h3>
            </div>
        </div>
      `).join('');
    })
    .catch(err => {
      console.error('Error al cargar resumen:', err);
      Swal.fire('Error', 'No se pudo cargar el resumen.', 'error');
    });
});
