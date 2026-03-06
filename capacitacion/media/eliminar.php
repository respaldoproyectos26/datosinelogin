<?php
require_once '../../helpers/db.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/url.php';
require_login();

$id = $_GET['id'] ?? null;
if (!$id) exit('ID inválido');

$stmt = db()->prepare("SELECT * FROM materiales WHERE id = ?");
$stmt->execute([$id]);
$material = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$material) exit('Material no encontrado');

$pageTitle = 'Eliminar material';
require_once '../../includes/header_base.php';
?>

<div class="container my-5">
  <h3 class="mb-4 text-danger"><i class="bi bi-trash"></i> Eliminar material</h3>
  <div class="card shadow-sm">
    <div class="card-body">
      <p>¿Seguro que deseas eliminar el material <strong><?= htmlspecialchars($material['titulo']) ?></strong>?</p>
      <button id="btnEliminar" class="btn btn-danger">Eliminar</button>
      <a href="../biblioteca/" class="btn btn-secondary">Cancelar</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('btnEliminar').addEventListener('click', () => {
  Swal.fire({
    title: 'Confirmar eliminación',
    text: 'Esta acción no se puede deshacer.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
  }).then(res => {
    if (res.isConfirmed) {
      fetch('../biblioteca/api/delete.php', {
        method: 'POST',
        body: new URLSearchParams({ id: '<?= $id ?>' })
      })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          Swal.fire('Eliminado', data.message, 'success').then(() => location.href = '../biblioteca/');
        } else {
          Swal.fire('Error', data.message || 'Error al eliminar.', 'error');
        }
      });
    }
  });
});
</script>

<?php require_once '../../includes/footer_base.php'; ?>
