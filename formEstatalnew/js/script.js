// ===== Helpers de UI =====
  const rmAccents = s => s.normalize('NFD').replace(/[\u0300-\u036f]/g,'');
  const toUpperNoAccents = el => el.value = rmAccents(el.value).toUpperCase();

  document.querySelectorAll('.text-uppercase').forEach(el=>{
    el.addEventListener('input', ()=> toUpperNoAccents(el));
  });

  // Solo dígitos (cel, telpart, cp, tel_proponente)
  ['cel','telpart','cp','tel_proponente'].forEach(id=>{
    const el = document.getElementById(id);
    if(el){ el.addEventListener('input', ()=> el.value = el.value.replace(/\D+/g,'').slice(0, el.id==='cp'?5:10)); }
  });

  // ===== Cascada Estado → Municipio → Sección =====
  const $estado = document.getElementById('estado');
  const $muni   = document.getElementById('municipio');
  const $seccion= document.getElementById('seccion');

  $estado?.addEventListener('change', ()=>{
    const v = $estado.value;
    $muni.innerHTML = '<option value="">Cargando...</option>';
    // TODO: ajusta la URL a tus endpoints reales
    fetch('api/get_municipios.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'idedo='+encodeURIComponent(v)
    }).then(r=>r.json()).then(data=>{
      $muni.innerHTML = '<option value="">Selecciona un municipio</option>';
      (data||[]).forEach(m=> $muni.insertAdjacentHTML('beforeend', `<option value="${m.municalc}">${m.municalc}</option>`));
      $seccion.innerHTML = '<option value="">Selecciona un municipio primero</option>';
    }).catch(()=>{ $muni.innerHTML='<option value="">Error al cargar, selecciona estado...</option>'; });
  });

  $muni?.addEventListener('change', ()=>{
    const v = $muni.value;
    $seccion.innerHTML = '<option value="">Cargando...</option>';
    // TODO: ajusta la URL a tus endpoints reales
    fetch('api/get_secciones.php', {
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'idmunicalc='+encodeURIComponent(v)
    }).then(r=>r.json()).then(data=>{
      $seccion.innerHTML = '<option value="">Selecciona una sección</option>';
      (data||[]).forEach(s=> $seccion.insertAdjacentHTML('beforeend', `<option value="${s.seccion}">${s.seccion}</option>`));
    }).catch(()=>{ $seccion.innerHTML='<option value="">Error al cargar, , selecciona municipio...</option>'; });
  });


  // --- SUBMIT (único listener)
  const form = document.getElementById('formCrear');
  if (form) {
    form.addEventListener('submit', function(e){
      e.preventDefault();
      const fd = new FormData(this);
      fetch('save_registrofig.php', { method:'POST', body: fd })
        .then(r=>r.json())
        .then(res=>{
          if(res.ok){
            Swal.fire({icon:'success',title:'Guardado',timer:1300,showConfirmButton:false})
              .then(()=>{ window.location.href = '../index.php'; });
          }else{
            Swal.fire({icon:'warning',title:'Atención',text: res.msg || 'No se pudo guardar'});
          }
        }).catch(()=> Swal.fire({icon:'error',title:'Error',text:'Fallo de red o servidor'}));
    });
  }

  // Deep-linking de tabs con hash
  (function(){
    const tablist  = document.getElementById('myTab');
    const panels   = document.getElementById('tabPanels');
    if(!tablist || !panels) return;

    const btns  = [...tablist.querySelectorAll('.nav-link[data-tab]')];
    const panes = [...panels.querySelectorAll('.tab-panel[data-tab]')];

    function activate(tab){
      btns.forEach(b => {
        const on = b.dataset.tab === tab;
        b.classList.toggle('active', on);
        b.setAttribute('aria-selected', on ? 'true':'false');
      });
      panes.forEach(p => { p.hidden = (p.dataset.tab !== tab); });
    }

    // abrir por hash si existe
    const fromHash = location.hash.replace('#','');
    if (fromHash && panes.some(p => p.dataset.tab === fromHash)) {
      activate(fromHash);
    }

    // actualizar hash al hacer click
    btns.forEach(b => b.addEventListener('click', () => {
      const tab = b.dataset.tab;
      history.replaceState(null, '', '#'+tab);
      activate(tab);
    }));
  })();

// --- BLOQUE ENLACES / PROPONENTE (hazlo opcional)
/*
const dl = document.getElementById('dl_enlaces');
const input = document.getElementById('estructasoc_input');
const hidden = document.getElementById('estructasoc');
const estadoSel = document.getElementById('idedo');

if (input && dl) {
  let t;
  input.addEventListener('input', () => {
    clearTimeout(t);
    t = setTimeout(() => {
      const q = input.value.trim();
      if (q.length < 2) return;
      const edo = encodeURIComponent(estadoSel?.value || '');
      fetch('api/search_enlaces.php?q='+encodeURIComponent(q)+'&estado='+edo)
        .then(r=>r.json()).then(list=>{
          dl.innerHTML = '';
          (list||[]).forEach(o => {
            const opt = document.createElement('option');
            opt.value = o.label;   // lo visible
            opt.dataset.id = o.id; // si quieres usar el id después
            dl.appendChild(opt);
          });
        });
    }, 250);
  });

  // Si quieres guardar el "label" tal cual en tu campo varchar
  input.addEventListener('change', ()=>{ if (hidden) hidden.value = input.value; });
}
 */

// --- Resumen dinámico (Nombre, Fecha, Sexo, etc.) ---
(function(){
  const get = id => document.getElementById(id);
  const setTxt = (id, v) => { const el = get(id); if(el) el.textContent = v || '—'; };

  function selText(el){
    if(!el) return '';
    if(el.tagName === 'SELECT'){
      const opt = el.options[el.selectedIndex];
      return (opt && opt.text) ? opt.text.trim() : (el.value || '').trim();
    }
    return (el.value || '').trim();
  }

  function fechaBonita(dia, mes, anio){
    if(!dia || !mes || !anio) return '';
    // día/mes/anio → 01/01/1992 (puedes cambiar a DD-MM-YYYY)
    return `${dia.padStart(2,'0')}/${mes.padStart(2,'0')}/${anio}`;
  }

  function mapSexo(v){
    if(v === 'M') return 'Hombre';
    if(v === 'F') return 'Mujer';
    return '';
  }

  function updateResumen(){
    // Nombre completo en MAYÚSCULAS y sin acentos (ya tienes helpers; aquí simple)
    const paterno = (get('paterno')?.value || '').toUpperCase();
    const materno = (get('materno')?.value || '').toUpperCase();
    const nombre  = (get('nombre')?.value  || '').toUpperCase();
    const nombreFull = [paterno, materno, nombre].filter(Boolean).join(' ');
    setTxt('sum-nombre', nombreFull);

    // Fecha
    const fecha = fechaBonita(get('dianac')?.value, get('mesnac')?.value, get('yearnac')?.value);
    setTxt('sum-fecha', fecha);

    // Sexo
    setTxt('sum-sexo', mapSexo(get('genero')?.value));

    // Clave Elector
    setTxt('sum-clave', get('clave_elector')?.value);

    // Estado y municipio (texto visible del select)
    setTxt('sum-estado', selText(get('idedo')));
    setTxt('sum-muni',   selText(get('idmunicalc')));

    // Celular y email
    setTxt('sum-cel',   get('cel')?.value);
    setTxt('sum-email', get('email')?.value);
  }

  // Suscribimos cambios relevantes
  [
    'paterno','materno','nombre','dianac','mesnac','yearnac',
    'genero','clave_elector','idedo','idmunicalc','cel','email'
  ].forEach(id=>{
    const el = get(id);
    if(!el) return;
    el.addEventListener('input', updateResumen);
    el.addEventListener('change', updateResumen);
  });

  // Estado inicial
  document.addEventListener('DOMContentLoaded', updateResumen);
  // y una llamada de cortesía por si el header se inyecta después
  setTimeout(updateResumen, 0);
})();
