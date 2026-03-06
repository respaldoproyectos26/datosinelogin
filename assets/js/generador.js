document.addEventListener('DOMContentLoaded', function () {
  const btnCopiar = document.getElementById('copiarHash');
  const inputHash = document.getElementById('passwordhash');

  if (btnCopiar && inputHash) {
    btnCopiar.addEventListener('click', () => {
      inputHash.select();
      inputHash.setSelectionRange(0, 99999); // móvil
      document.execCommand('copy');

      Swal.fire({
        icon: 'success',
        title: 'Copiado',
        text: 'El hash ha sido copiado al portapapeles.',
        timer: 2000,
        showConfirmButton: false
      });
    });
  }
});
