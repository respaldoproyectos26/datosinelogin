<?php
require_once __DIR__ . '/../helpers/bootstrap.php';
//require_once "../helpers/helpers_exams.php";
require_login();
csrf_check(); 
set_cache_headers('html-nocache'); 
render_flash_swals();

if (!tiene_permiso('presentar_evaluaciones')) denegar_acceso();

$exam_id = (int)($_GET['id'] ?? 0);
if ($exam_id<=0) { echo "ID inválido"; exit; }

$e = db()->prepare("SELECT * FROM exams WHERE id=?");
$e->execute([$exam_id]);
$exam = $e->fetch(PDO::FETCH_ASSOC);
if (!$exam) { echo "Examen no encontrado"; exit; }

require_once "../includes/headerfiltrodatos.php";
?>
<div class="container my-4">
  <h3><?= htmlspecialchars($exam['title']) ?></h3>
  <p class="text-muted"><?= nl2br(htmlspecialchars($exam['description'])) ?></p>

  <div class="alert alert-info d-flex justify-content-between align-items-center">
    <div>
      <b>Tiempo:</b> <?= (int)$exam['time_limit_min'] ?> min |
      <b>Intentos permitidos:</b> <?= (int)$exam['attempts_allowed'] ?>
    </div>
    <div><b id="timer">00:00</b></div>
  </div>

  <div id="exam-area" class="card p-3 bg-white">
    <button id="btnStart" class="btn btn-primary">Comenzar intento</button>
  </div>
</div>

<script>
let attemptId = null;
let endAt = null;
let timeLimit = <?= (int)$exam['time_limit_min'] ?>;

async function startAttempt() {
  const fd = new FormData();
  fd.append('exam_id', '<?= $exam_id ?>');
  const r = await fetch('api/exams/start.php', { method:'POST', body: fd });
  const j = await r.json();
  if(!j.ok){ alert(j.error||'No se pudo iniciar'); return; }
  attemptId = j.attempt_id;
  renderQuestions(j.questions);
  startTimer(j.exam.time_limit_min);
}

function renderQuestions(questions){
  const area = document.getElementById('exam-area');
  area.innerHTML = '';
  const form = document.createElement('form');
  form.id = 'examForm';
  form.className = 'vstack gap-3';

  questions.forEach(q=>{
    const card = document.createElement('div');
    card.className = 'border rounded p-3';
    let html = `<div class="mb-2"><b>[${q.type}]</b> ${escapeHtml(q.stem)}</div>`;
    if (q.media_url){
      if(q.media_type && q.media_type.startsWith('image/')){
        html += `<img src="${q.media_url}" class="img-fluid mb-2">`;
      } else if (q.media_type && q.media_type.startsWith('audio/')){
        html += `<audio controls class="mb-2" src="${q.media_url}"></audio>`;
      }
    }
    if (q.type==='text') {
      html += `<textarea class="form-control" name="t_${q.id}" rows="3"></textarea>`;
    } else {
      q.options.forEach(o=>{
        html += `
          <div class="form-check">
            <input class="form-check-input" type="radio" name="q_${q.id}" value="${o.id}" id="q_${q.id}_${o.id}">
            <label class="form-check-label" for="q_${q.id}_${o.id}">${escapeHtml(o.label)}</label>
          </div>`;
      });
    }
    card.innerHTML = html;
    form.appendChild(card);
  });

  const btn = document.createElement('button');
  btn.type = 'button';
  btn.className = 'btn btn-success';
  btn.textContent = 'Enviar intento';
  btn.onclick = submitAttempt;
  form.appendChild(btn);

  area.appendChild(form);
}

function startTimer(mins){
  const t = document.getElementById('timer');
  const end = Date.now() + (mins>0? mins*60*1000 : 0);
  endAt = (mins>0)? end : null;

  function tick(){
    if(!endAt){ t.textContent = 'Sin límite'; return; }
    const s = Math.max(0, Math.floor((endAt - Date.now())/1000));
    const mm = Math.floor(s/60), ss = s%60;
    t.textContent = mm+":"+String(ss).padStart(2,'0');
    if (s<=0) { submitAttempt(); return; }
    requestAnimationFrame(tick);
  }
  tick();
}

async function submitAttempt(){
  const form = document.getElementById('examForm');
  if(!form){ alert('No hay preguntas'); return; }
  const fd = new FormData(form);
  fd.append('attempt_id', attemptId);
  const r = await fetch('api/exmas/submit.php', { method:'POST', body: fd });
  const j = await r.json();
  if(!j.ok){ alert(j.error||'No se pudo enviar'); return; }
  alert('Intento enviado. Puntaje: '+j.score);
  location.href = 'index.php';
}

function escapeHtml(s){ return s.replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }
document.getElementById('btnStart').addEventListener('click', startAttempt);
</script>
<?php require_once "../includes/footerfiltrodatos.php"; ?>
