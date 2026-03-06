<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
//require_once "../helpers/helpers_exams.php";
require_login();
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

$attemptId = (int)($_GET['id'] ?? 0);
if ($attemptId<=0) { die("ID inválido"); }

$pdo = db();
$st = $pdo->prepare("
  SELECT a.*, e.title, e.show_correct_answers, e.show_review, e.show_score,
         u.usuario, e.id AS exam_id
  FROM attempts a
  JOIN exams e ON e.id=a.exam_id
  JOIN ine_usuarios u ON u.id=a.user_id
  WHERE a.id=?
");
$st->execute([$attemptId]);
$att = $st->fetch(PDO::FETCH_ASSOC);
if (!$att) die("Intento no encontrado");

// Permisos: admin puede ver todo; el usuario solo su intento
if (!tiene_permiso('ver_resultados_evaluaciones') && (!isset($_SESSION['user']['id']) || (int)$att['user_id'] !== (int)$_SESSION['user']['id'])) {
  denegar_acceso();
}

// Traer preguntas y respuestas
$q = $pdo->prepare("SELECT id,type,stem,points FROM questions WHERE exam_id=? ORDER BY id ASC");
$q->execute([$att['exam_id']]);
$questions = $q->fetchAll(PDO::FETCH_ASSOC);

$ans = $pdo->prepare("SELECT question_id, option_id, text_answer FROM answers WHERE attempt_id=?");
$ans->execute([$attemptId]);
$answers = [];
foreach ($ans as $a) $answers[(int)$a['question_id']] = $a;

$opt = $pdo->prepare("SELECT id,label,is_correct FROM options WHERE question_id=? ORDER BY id ASC");

require_once "../includes/headerfiltrodatos.php";
?>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Revisión del intento</h3>
    <a class="btn btn-secondary" href="resultados.php?exam_id=<?= (int)$att['exam_id'] ?>">Volver</a>
  </div>

  <!-- Resumen -->
  <div class="row g-3 mb-3">
    <div class="col-md-3">
      <div class="card p-3">
        <div class="text-muted">Usuario</div>
        <div class="fw-bold"><?= htmlspecialchars($att['usuario']) ?></div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3">
        <div class="text-muted">Examen</div>
        <div class="fw-bold"><?= htmlspecialchars($att['title']) ?></div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card p-3">
        <div class="text-muted">Puntaje</div>
        <div class="fw-bold">
          <?php if ($att['show_score'] || tiene_permiso('ver_resultados_evaluaciones')): ?>
            <?= number_format((float)$att['score'], 2) ?>
          <?php else: ?>
            —
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card p-3">
        <div class="text-muted">Duración</div>
        <div class="fw-bold"><?= gmdate('i:s', (int)$att['duration_sec']) ?></div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="card p-3">
        <div class="text-muted">Fecha</div>
        <div class="fw-bold"><?= htmlspecialchars($att['submitted_at']) ?></div>
      </div>
    </div>
  </div>

  <!-- Detalle por pregunta -->
  <?php foreach ($questions as $q): 
    $a = $answers[$q['id']] ?? null;
    $userOptId = $a['option_id'] ?? null;
    $userText  = $a['text_answer'] ?? '';
    $status = 'open';
    $isCorrect = null;

    if ($q['type'] !== 'text' && $userOptId) {
      $ck = $pdo->prepare("SELECT is_correct FROM options WHERE id=? AND question_id=?");
      $ck->execute([$userOptId, $q['id']]);
      $isCorrect = (int)$ck->fetchColumn() === 1;
      $status = $isCorrect ? 'ok' : 'bad';
    }

    // Colores (verde/rojo/gris)
    $bg = $status==='ok' ? 'border-success' : ($status==='bad' ? 'border-danger' : 'border-secondary');
  ?>
  <div class="card mb-3 <?= $bg ?>">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <div>
          <b>[<?= $q['type'] ?>]</b> <?= htmlspecialchars($q['stem']) ?>
          <span class="ms-2 badge bg-light text-dark"><?= number_format($q['points'],2) ?> pts</span>
        </div>
        <div>
          <?php if ($status==='ok'): ?>
            <span class="badge bg-success">✅ Correcta</span>
          <?php elseif ($status==='bad'): ?>
            <span class="badge bg-danger">❌ Incorrecta</span>
          <?php else: ?>
            <span class="badge bg-secondary">✍️ Abierta</span>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($q['type']!=='text'): 
        $opt->execute([$q['id']]);
        $opts = $opt->fetchAll(PDO::FETCH_ASSOC);
      ?>
        <ul class="mt-2 mb-0">
          <?php foreach ($opts as $o):
            $isUser = ($userOptId && (int)$userOptId === (int)$o['id']);
            $cls = $isUser ? 'fw-bold' : '';
            $chip = '';
            if ($isUser) $chip .= ' (tu elección)';
            if (($att['show_correct_answers'] || tiene_permiso('ver_resultados_evaluaciones')) && (int)$o['is_correct']===1) {
              $chip .= ' — correcta';
            }
          ?>
            <li class="<?= $cls ?>"><?= htmlspecialchars($o['label']) ?> <small class="text-muted"><?= $chip ?></small></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <div class="mt-2">
          <div class="text-muted">Tu respuesta:</div>
          <div class="border rounded p-2 bg-light"><?= nl2br(htmlspecialchars($userText)) ?></div>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php require_once "../includes/footerfiltrodatos.php"; ?>
