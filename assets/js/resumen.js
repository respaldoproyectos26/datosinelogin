document.addEventListener('DOMContentLoaded', () => {
  fetch('../../api/resumen_api.php') // usa este alias en vez del archivo resumen original
    .then(r => r.json())
    .then(data => {
      document.getElementById('total').textContent = data.total;
      document.getElementById('validos').textContent = data.curps_validos;
      document.getElementById('invalidos').textContent = data.curps_invalidos;
    })
    .catch(err => {
      console.error("Error cargando resumen:", err);
      Swal.fire("Error", "No se pudo cargar el resumen", "error");
    });
});
