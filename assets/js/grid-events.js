document.addEventListener('click', function (e) {
  if (e.target.matches('[data-edit]')) {
    const id = e.target.dataset.edit;
    alert(`Editar registro ${id}`);
    // Aquí puedes abrir el modal y llenar con fetch
  }

  if (e.target.matches('[data-del]')) {
    const id = e.target.dataset.del;
    if (confirm(`¿Seguro que deseas eliminar el registro ${id}?`)) {
      fetch(`api/delete.php?id=${id}`, { method: 'DELETE' })
        .then(res => res.json())
        .then(resp => {
          alert(resp.msg);
          gridOptions.api.refreshInfiniteCache();
        });
    }
  }
});

document.getElementById('crudForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const form = e.target;
  const data = new FormData(form);  // para incluir archivo

  fetch('api/guardar_registro.php', {
    method: 'POST',
    body: data
  })
  .then(r => r.json())
  .then(resp => {
    if (resp.ok) {
      Swal.fire("Guardado", resp.msg, "success");
      bootstrap.Modal.getInstance(document.getElementById('crudModal')).hide();
      gridOptions.api.refreshInfiniteCache();
    } else {
      Swal.fire("Error", resp.msg, "error");
    }
  });
});