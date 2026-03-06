document.addEventListener('DOMContentLoaded', () => {
  const btnEntrada = document.getElementById('btnEntrada');
  const btnSalida = document.getElementById('btnSalida');
  const mensaje = document.getElementById('mensaje');
  const tablaAsistencia = document.getElementById('tablaAsistencia');

  const cargarHistorial = async () => {
    const res = await fetch('api/get_asistencias.php');
    tablaAsistencia.innerHTML = await res.text();
  };

  btnEntrada.addEventListener('click', async () => {
    const res = await fetch('api/registrar_entrada.php', { method: 'POST' });
    const data = await res.json();
    mensaje.textContent = data.mensaje;
    btnEntrada.disabled = true;
    btnSalida.disabled = false;
    cargarHistorial();
  });

  btnSalida.addEventListener('click', async () => {
    const res = await fetch('api/registrar_salida.php', { method: 'POST' });
    const data = await res.json();
    mensaje.textContent = data.mensaje;
    btnSalida.disabled = true;
    cargarHistorial();
  });

  cargarHistorial();
});