<?php
require_once __DIR__ . '/../../helpers/bootstrap.php';
require_login(); csrf_check(); set_cache_headers('html-nocache');
if (!can('editar_material')) { flash('error','Sin permiso'); redirect('index.php'); }

$id = (int)($_GET['id'] ?? 0);
$item = db()->prepare("SELECT * FROM media WHERE id=?"); $item->execute([$id]); $media=$item->fetch(PDO::FETCH_ASSOC);
if(!$media){ flash('error','Material no encontrado'); redirect('index.php'); }

$pageTitle='Editar Material';
$pageHeader='Editar Material';
require_once __DIR__ . '/../../includes/headerfiltrodatos.php';
?>
<div class="container py-4">
  <h2 class="fw-bold mb-4"><i class="bi bi-pencil"></i> Editar Material</h2>

  <form method="POST" id="editForm" enctype="multipart/form-data">
    <?php csrf_input(); ?>
    <input type="hidden" name="id" value="<?= $id ?>">

    <div class="mb-3"><label class="form-label">Título</label>
      <input type="text" name="titulo" class="form-control" value="<?= e($media['titulo']) ?>" required></div>

    <div class="mb-3"><label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control"><?= e($media['descripcion']) ?></textarea></div>

    <div class="mb-3"><label class="form-label">Categoría</label>
      <input type="text" name="categoria" class="form-control" value="<?= e($media['categoria']) ?>"></div>

    <div class="mb-3">
      <label class="form-label">Reemplazar archivo (opcional)</label>
      <input type="file" name="archivo" class="form-control">
    </div>

    <button class="btn btn-success"><i class="bi bi-save"></i> Guardar cambios</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
<script>
document.getElementById('editForm').onsubmit=async(e)=>{
  e.preventDefault();
  const fd=new FormData(e.target);
  const r=await fetch('api/update.php',{method:'POST',headers:{'X-CSRF':'<?= csrf_token() ?>'},body:fd});
  const j=await r.json();
  if(j.ok){Swal.fire('Actualizado','Cambios guardados','success').then(()=>location='index.php');}
  else Swal.fire('Error',j.error||'Error','error');
};
</script>
<?php require_once __DIR__ . '/../../includes/footerfiltrodatos.php'; ?>
