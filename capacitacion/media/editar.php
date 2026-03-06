<?php
require_once "../../includes/headerfiltrodatos.php";
require_once "../../helpers/db.php";
require_once "../../helpers/auth.php";
require_once "../../helpers/helpers_media.php";

if (!auth()->can('editar_material')) denegar_acceso();
$id = (int)($_GET['id'] ?? 0);
$pdo = db();
$it = $pdo->prepare("SELECT * FROM media WHERE id=?");
$it->execute([$id]);
$item = $it->fetch(PDO::FETCH_ASSOC);
if(!$item) die("No encontrado");
?>
<div class="container py-4">
  <h3 class="fw-bold mb-3">Editar material</h3>
  <form id="fEdit">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="mb-3">
      <label class="form-label">Título</label>
      <input class="form-control" name="titulo" value="<?= htmlspecialchars($item['titulo']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descripción</label>
      <textarea class="form-control" name="descripcion"><?= htmlspecialchars($item['descripcion']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Categoría</label>
      <input class="form-control" name="categoria" value="<?= htmlspecialchars($item['categoria']) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Reemplazar archivo (opcional)</label>
      <input type="file" name="archivo" class="form-control">
      <div class="form-text">Si subes un archivo, se reemplaza el actual. Para links, edítalo abajo.</div>
    </div>
    <div class="mb-3">
      <label class="form-label">Link externo (opcional)</label>
      <input type="url" name="link" class="form-control" placeholder="https://..." value="<?= $item['tipo']==='link' ? htmlspecialchars($item['archivo']) : '' ?>">
    </div>
    <button class="btn btn-primary">Guardar cambios</button>
    <a href="index.php" class="btn btn-secondary ms-2">Cancelar</a>
  </form>
</div>
<script>
document.getElementById('fEdit').onsubmit = async (e)=>{
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await fetch('api/update.php', { method:'POST', body: fd });
  const j = await res.json();
  if (j.ok) location.href='index.php'; else alert(j.error||'Error');
};
</script>
<?php require_once "../../includes/footerfiltrodatos.php"; ?>
