<?php
require_once "../../includes/headerfiltrodatos.php";
require_once "../../helpers/db.php";
require_once "../../helpers/auth.php";
require_once "../../helpers/helpers_media.php";

if (!auth()->can('ver_biblioteca')) { denegar_acceso(); }

$id = (int)($_GET['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare("SELECT * FROM media WHERE id=?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$item) die("No encontrado");

// Marcar visto una vez
$pdo->prepare("INSERT IGNORE INTO media_vistos (media_id,user_id) VALUES (?,?)")->execute([$id, auth()->id()]);
?>
<div class="container py-4">
  <h2 class="fw-bold mb-3"><?= htmlspecialchars($item['titulo']) ?></h2>
  <p class="text-muted"><?= nl2br(htmlspecialchars($item['descripcion'])) ?></p>

  <div class="ratio ratio-16x9 mb-4 border rounded overflow-hidden" id="viewer">
    <?php if ($item['tipo']==='link'): ?>
      <?= media_embed_link($item['archivo']); ?>
    <?php elseif (str_starts_with($item['tipo']??'', 'image')): ?>
      <img src="../../uploads/media/<?= htmlspecialchars($item['archivo']) ?>" class="img-fluid">
    <?php elseif (($item['tipo']??'')==='application/pdf'): ?>
      <iframe src='../../uploads/media/<?= htmlspecialchars($item['archivo']) ?>#toolbar=0'></iframe>
    <?php elseif (str_contains($item['tipo']??'', 'video')): ?>
      <video controls src='../../uploads/media/<?= htmlspecialchars($item['archivo']) ?>'></video>
    <?php else: ?>
      <div class="d-flex align-items-center justify-content-center p-4 bg-light">
        <a class="btn btn-primary" href='../../uploads/media/<?= htmlspecialchars($item['archivo']) ?>' target="_blank" rel="noopener">Descargar</a>
      </div>
    <?php endif; ?>
  </div>

  <a href="index.php" class="btn btn-secondary">Volver</a>
</div>

<script>
// ===== Sesión de visualización (opción B: inicio + fin)
let sessionId = null;
let startTs = Date.now();

(async function start(){
  const res = await fetch('api/start_view.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body: new URLSearchParams({ media_id: '<?= $id ?>' })
  });
  const j = await res.json();
  if (j.ok) sessionId = j.session_id;
})();

function endSession(){
  if (!sessionId) return;
  const elapsed = Math.round((Date.now()-startTs)/1000);
  const data = new URLSearchParams({ session_id: sessionId, elapsed });
  // usar sendBeacon para asegurar envío al cerrar
  navigator.sendBeacon('api/end_view.php', data);
}

window.addEventListener('beforeunload', endSession);
</script>

<?php require_once "../../includes/footerfiltrodatos.php"; ?>
