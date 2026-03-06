<?php
require_once __DIR__ . '/../../helpers/bootstrap.php';
require_once __DIR__ . '/../../helpers/helpers_media.php';
require_login(); csrf_check(); set_cache_headers('html-nocache');
if (!can('ver_biblioteca')) exit('Acceso denegado');

$id = (int)($_GET['id'] ?? 0);
$pdo = db();
$it = $pdo->prepare("SELECT * FROM media WHERE id=?"); $it->execute([$id]); $item=$it->fetch(PDO::FETCH_ASSOC);
if(!$item) exit('No encontrado');

// marcar visto (una vez)
$pdo->prepare("INSERT IGNORE INTO media_vistos (media_id,user_id) VALUES (?,?)")->execute([$id,current_user_id()]);

$pageTitle='Ver material'; $pageHeader=htmlspecialchars($item['titulo']);
require_once __DIR__ . '/../../includes/headerfiltrodatos.php';
?>
<div class="container py-4">
  <h2 class="fw-bold mb-3"><?= htmlspecialchars($item['titulo']) ?></h2>
  <p class="text-muted"><?= nl2br(htmlspecialchars($item['descripcion'])) ?></p>

  <div class="ratio ratio-16x9 mb-4 border rounded overflow-hidden">
    <?php if ($item['tipo']==='link'): ?>
      <?= media_embed_link($item['archivo']); ?>
    <?php elseif (str_starts_with($item['tipo']??'', 'image')): ?>
      <img src="uploads/<?= htmlspecialchars($item['archivo']) ?>" class="img-fluid">
    <?php elseif (($item['tipo']??'')==='application/pdf'): ?>
      <iframe src="uploads/<?= htmlspecialchars($item['archivo']) ?>#toolbar=0"></iframe>
    <?php elseif (str_contains($item['tipo']??'', 'video')): ?>
      <video controls src="uploads/<?= htmlspecialchars($item['archivo']) ?>"></video>
    <?php else: ?>
      <div class="d-flex align-items-center justify-content-center p-4 bg-light">
        <?php if ($item['tipo']==='link'): ?>
          <a class="btn btn-primary" href="<?= htmlspecialchars($item['archivo']) ?>" target="_blank" rel="noopener">Abrir enlace</a>
        <?php else: ?>
          <a class="btn btn-primary" href="uploads/<?= htmlspecialchars($item['archivo']) ?>" target="_blank" rel="noopener">Descargar</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <a href="index.php" class="btn btn-secondary">Volver</a>
</div>

<script>
let sessionId=null, startTs=Date.now();
(async function(){
  const res = await fetch('api/start_view.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF':'<?= csrf_token() ?>'},
    body:new URLSearchParams({ media_id:'<?= $id ?>' })
  });
  const j=await res.json(); if(j.ok) sessionId=j.session_id;
})();

function endSession(){
  if(!sessionId) return;
  const elapsed=Math.round((Date.now()-startTs)/1000);
  const data=new FormData(); data.append('session_id',sessionId); data.append('elapsed',elapsed);
  navigator.sendBeacon('api/end_view.php', data);
}
window.addEventListener('beforeunload', endSession);
</script>
<?php require_once __DIR__ . '/../../includes/footerfiltrodatos.php'; ?>
