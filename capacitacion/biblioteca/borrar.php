<?php
require_once __DIR__ . '/../../helpers/bootstrap.php';
require_login(); csrf_check(); set_cache_headers('html-nocache');
if (!can('eliminar_material')) { flash('error','Sin permiso'); redirect('index.php'); }

$id = (int)($_GET['id'] ?? 0);
$item = db()->prepare("SELECT * FROM media WHERE id=?"); $item->execute([$id]); $media=$item->fetch(PDO::FETCH_ASSOC);
if(!$media){ flash('error','Material no encontrado'); redirect('index.php'); }

$pageTitle='Eliminar Material'; $pageHeader='Confirmar eliminación';
require_once __DIR__ . '/../../includes/headerfiltrodatos.php';
?>
<div class="container py-4 text-center">
  <h2 class="fw-bold text-danger">¿Eliminar este material?</h2>
  <p class="lead"><?= e($media['titulo']) ?></p>
  <form method="POST" id="delForm">
    <?php csrf_input(); ?><input type="hidden" name="id" value="<?= $id ?>">
    <button class="btn btn-danger"><i class="bi bi-trash"></i> Eliminar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
<script>
document.getElementById('delForm').onsubmit=async(e)=>{
  e.preventDefault();
  const fd=new FormData(e.target);
  const r=await fetch('api/delete.php',{method:'POST',headers:{'X-CSRF':'<?= csrf_token() ?>'},body:fd});
  const j=await r.json();
  if(j.ok){Swal.fire('Eliminado','Material borrado','success').then(()=>location='index.php');}
  else Swal.fire('Error',j.error||'Error','error');
};
</script>
<?php require_once __DIR__ . '/../../includes/footerfiltrodatos.php'; ?>
