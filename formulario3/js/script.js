// =========================================================
// formulario3/js/script.js
// - SUBMIT único (evita duplicados)
// - Validación: enfoca el tab correcto
// - Lock anti doble-click
// - Marcar inválidos según backend (res.faltan)
// - CP con TomSelect (search_cp.php) + aplicarCP(get_cp.php) + reset limpio
// - Mantiene tu cascada Estado -> Municipio -> Sección
// - Mantiene previews de foto + foto INE
// =========================================================

// ===== Helpers UI =====
const rmAccents = (s) => String(s ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
const toUpperNoAccents = (el) => { el.value = rmAccents(el.value).toUpperCase(); };

// Si quieres captura consecutiva (INSERT sin salir)
const PRESERVE_AFTER_INSERT = ['idedo', 'idmunicalc', 'seccion', 'estadodom'];

// ===== CONFIG ENDPOINTS =====
const SAVE_URL              = 'save_registrofig.php';
const API_GET_REGISTRO_URL  = 'api/get_registrofig.php';
const API_MUNICIPIOS_URL    = 'api/get_municipios.php';
const API_SECCIONES_URL     = 'api/get_secciones.php';

const API_CP_URL            = 'api/get_cp.php';
const API_SEARCH_CP_URL     = 'api/search_cp.php';

const API_SEARCH_PROP_URL   = 'api/search_figuras.php'; // (austero) proponente

// Ajusta según tu pantalla/listado
const REDIRECT_AFTER_SAVE = false; // <- ya no fijo
//const REDIRECT_AFTER_SAVE = true; // <- fijo
const REDIRECT_URL = '../filtrodatos3/';
//const REDIRECT_URL = '../index.php';

// Ajusta scope si tu endpoint lo requiere
const PROP_SCOPE = 'PROMOVIDO'; // RG | RC | PROMOVIDO

const LOCK_MUNI_BY_CP = false; // o true si lo necesitas
function lockMunicipioByCP(on) {
  const muni = document.getElementById('idmunicalc');
  if (!muni) return;
  muni.disabled = !!on;
  if (on) muni.removeAttribute('required');
  else muni.setAttribute('required', 'required');
}

// ===== MAPEO (API/DB -> FORM) =====
const FIELD_MAP = {
  // Identidad
  paterno: 'paterno',
  materno: 'materno',
  nombre:  'nombre',
  fechanac: ['dianac','mesnac','yearnac'],
  genero: 'genero',

  // Estado representado
  estadodom: 'estadodom',

  // Domicilio
  estado:   'idedo',
  municalc: 'idmunicalc',
  seccion:  'seccion',
  calle:    'calle',
  numext:   'numext',
  numint:   'numint',
  colonia:  'colonia',
  cp:       'cp',

  // Contacto
  cel:        'cel',
  telpart:    'telpart',
  teloficina: 'teloficina',
  email:      'email',
  facebook:   'facebook',
  twitter:    'twitter',
  instag:     'instag',
  observacion:'observacion',

  // Extra
  escolaridad:   'escolaridad',
  clave_elector: 'clave_elector',

  // Copias/afinidad
  copia_credelec: 'copia_credelec',
  foto_ine: 'foto_ine', // preview

  afinidad: 'afinidad',
  otro:     'otro',

  // Representación
  fechaform:     'fecha_form',
  folioform:     'folio_form',
  estado_folio:  'edo_form',
  dttofed_folio: 'distfed_form',
  tipo_folio:    'tipo_form',
  num_ruta:      'num_ruta',
  cadenaruta:    'secciones_ruta',

  // Proponente
  estrucasoc:  'estrucasoc',
  nombre_prop: 'estrucasoc',
  cargo_prop:  'cargo_proponente',
  tel_prop:    'tel_proponente',

  // Otros
  militante:  'militante',
  num_milit:  'num_milit',
  otraelec:   'otraelec',
  
  // Foto (solo preview)
  foto: "foto"
};

// ===== Normalizadores =====
const NORMALIZERS = {
  genero: v => (v || '').toUpperCase()
    .replace('HOMBRE','M').replace('MUJER','F')
    .replace('MASCULINO','M').replace('FEMENINO','F'),

  copia_credelec: v => {
    const s = String(v ?? '').trim().toUpperCase();
    if (s === 'SI') return 'SI';
    if (s === 'NO') return 'NO';
    return 'NO';
  },

  cp: v => String(v||'').replace(/\D+/g,'').slice(0,5),
  cel: v => String(v||'').replace(/\D+/g,'').slice(0,10),
  telpart: v => String(v||'').replace(/\D+/g,'').slice(0,10),
  teloficina: v => String(v||'').replace(/\D+/g,'').slice(0,10),
  tel_proponente: v => String(v||'').replace(/\D+/g,'').slice(0,10),

  clave_elector: v => String(v||'').toUpperCase().replace(/[^A-Z0-9]/g,''),

  paterno: v => rmAccents(String(v||'').toUpperCase()),
  materno: v => rmAccents(String(v||'').toUpperCase()),
  nombre:  v => rmAccents(String(v||'').toUpperCase()),
  estadodom: v => String(v||'').toUpperCase(),
  estado:    v => String(v||'').toUpperCase(),
  municalc:  v => String(v||'').toUpperCase(),
};

// ===== Utils DOM =====
function setSelectValue(sel, value) {
  if (!sel) return;
  const val = value == null ? "" : String(value);
  const exists = [...sel.options].some(o => String(o.value) === val);
  if (!exists && val !== "") sel.insertAdjacentHTML('beforeend', `<option value="${val}">${val}</option>`);
  sel.value = val;
}

function parseFechaNac(any) {
  if (!any) return null;
  const s = String(any).trim();
  let m = s.match(/^(\d{4})-(\d{2})-(\d{2})$/);
  if (m) return { yyyy:m[1], mm:m[2], dd:m[3] };
  m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
  if (m) return { yyyy:m[3], mm:m[2], dd:m[1] };
  return null;
}

function setFieldByName(name, value) {
  if (!name) return;
  const el = document.querySelector(`[name="${name}"]`);
  if (!el) return;

  let v = value;
  if (NORMALIZERS[name]) v = NORMALIZERS[name](value);

  if (el.tagName === 'SELECT') setSelectValue(el, v);
  else if (el.tagName === 'INPUT' && el.type !== 'file') el.value = v ?? '';
  else if (el.tagName === 'TEXTAREA') el.value = v ?? '';
}

function normalizeRows(payload) {
  return Array.isArray(payload) ? payload : (payload?.data ?? []);
}

// ===== Validación UX (tabs) =====
function resetInvalid() {
  document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
}

function markInvalid(keys) {
  // Mapea claves del backend a IDs reales si difieren
  const MAP = { calle:'calle', idedo:'idedo', idmunicalc:'idmunicalc' };
  (keys || []).forEach(k => {
    const id = MAP[k] || k;
    const el = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
    if (el) el.classList.add('is-invalid');
  });
}

function focusFirstMarkedInvalid(form) {
  const el = form.querySelector('.is-invalid');
  if (!el) return false;

  openTabFor(el);

  setTimeout(() => {
    try { el.focus({ preventScroll: false }); } catch {}
  }, 50);

  return true;
}

function openTabFor(el) {
  const panel = el.closest('.tab-panel[data-tab]');
  if (!panel) return;
  const tab = panel.dataset.tab;
  const btn = document.querySelector(`#myTab .nav-link[data-tab="${tab}"]`);
  if (btn) btn.click();
}

function focusFirstInvalid(form) {
  const firstInvalid = form.querySelector(':invalid');
  if (!firstInvalid) return false;

  openTabFor(firstInvalid);

  setTimeout(() => {
    try { firstInvalid.focus({ preventScroll:false }); } catch {}
    form.reportValidity();
  }, 50);

  return true;
}

// ===== Previews (foto + INE) =====
let FOTO_PLACEHOLDER_SRC = null;

function setPathToImg(imgEl, path, fallbackSrc = null) {
  if (!imgEl) return;
  if (!path || typeof path !== 'string' || !path.trim()) {
    if (fallbackSrc) imgEl.src = fallbackSrc;
    return;
  }
  const clean = path.trim();
  const isAbs = /^https?:\/\//i.test(clean);
  const pref  = clean.startsWith('../uploads/') || clean.startsWith('/uploads/');
  imgEl.src = (isAbs || pref) ? clean : `../uploads/${clean.replace(/^\/+/, '')}`;
}

function setFotoPreviewFromData(data) {
  const img = document.getElementById('perfilFotoPreview');
  if (!img) return;
  setPathToImg(img, data?.foto, img.getAttribute('src') || img.src);
}

function resetFotoPreview() {
  const img = document.getElementById('perfilFotoPreview');
  const input = document.getElementById('foto');
  if (input) input.value = '';
  if (img && FOTO_PLACEHOLDER_SRC) img.src = FOTO_PLACEHOLDER_SRC;
}

function setInePreviewFromData(data) {
  const img = document.getElementById('ineFotoPreview');
  if (!img) return;
  const fallback = window.INE_PLACEHOLDER_SRC || img.getAttribute('src') || img.src;
  setPathToImg(img, data?.foto_ine, fallback);
}

function toggleCopiaIneUI() {
  const sel   = document.getElementById('copia_credelec');
  const block = document.getElementById('copiaIneBlock');
  const input = document.getElementById('foto_ine');
  const img   = document.getElementById('ineFotoPreview');
  if (!sel || !block) return;

  const on = String(sel.value || '').toUpperCase() === 'SI';

  // show/hide
  block.classList.toggle('d-flex', on);
  block.classList.toggle('d-none', !on);

  // required condicionado
  if (input) {
    if (on) input.setAttribute('required', 'required');
    else input.removeAttribute('required');
  }

  // limpiar si NO
  if (!on) {
    if (input) input.value = '';
    if (img && window.INE_PLACEHOLDER_SRC) img.src = window.INE_PLACEHOLDER_SRC;
  }
}

function initFotoUI() {
  const input = document.getElementById('foto');
  const btnSel = document.getElementById('btnSelectFoto');
  const btnUp  = document.getElementById('btnActualizarFoto');
  const img    = document.getElementById('perfilFotoPreview');

  if (img && !FOTO_PLACEHOLDER_SRC) FOTO_PLACEHOLDER_SRC = img.getAttribute('src') || img.src;
  if (btnSel && input) btnSel.addEventListener('click', () => input.click());

  if (input && img) {
    input.addEventListener('change', () => {
      const f = input.files?.[0];
      if (!f) return;

      if (!/image\/(jpeg|png)/.test(f.type)) {
        Swal.fire('Formato no válido', 'Usa JPG o PNG', 'warning');
        input.value = '';
        return;
      }
      if (f.size > 3 * 1024 * 1024) {
        Swal.fire('Archivo muy grande', 'Máximo 3MB', 'warning');
        input.value = '';
        return;
      }
      img.src = URL.createObjectURL(f);
    });
  }

  if (btnUp) {
    btnUp.addEventListener('click', () => {
      Swal.fire('Listo', 'La foto se actualizará al guardar el formulario', 'info');
    });
  }
}

function initFotoIneUI() {
  const input = document.getElementById('foto_ine');
  const btnSel = document.getElementById('btnSelectFotoIne');
  const img = document.getElementById('ineFotoPreview');

  if (img && !window.INE_PLACEHOLDER_SRC) {
    window.INE_PLACEHOLDER_SRC = img.getAttribute('src') || img.src;
  }
  if (btnSel && input) btnSel.addEventListener('click', () => input.click());

  if (input && img) {
    input.addEventListener('change', () => {
      const f = input.files?.[0];
      if (!f) return;

      if (!/image\/(jpeg|png)/.test(f.type)) {
        Swal.fire('Formato no válido', 'Usa JPG o PNG', 'warning');
        input.value = '';
        return;
      }
      if (f.size > 3 * 1024 * 1024) {
        Swal.fire('Archivo muy grande', 'Máximo 3MB', 'warning');
        input.value = '';
        return;
      }
      img.src = URL.createObjectURL(f);
    });
  }
}

// ===== Afinidad / Otro =====
function setAfinidad(data) {
  const sel = document.getElementById('afinidad');
  const afin = data?.afinidad || '';
  if (!sel || !afin) return;

  if (![...sel.options].some(o => o.value === 'otro')) {
    sel.insertAdjacentHTML('beforeend', `<option value="otro">Otro</option>`);
  }
  setSelectValue(sel, afin);

  if (String(afin).toLowerCase() === 'otro' && data?.otro) {
    setFieldByName('otro', data.otro);
    if (typeof mostrarCampoOtro === 'function') mostrarCampoOtro({ value: 'otro' });
  }
}

// ===== Cascada Estado → Municipio → Sección =====
const $estado  = document.getElementById('idedo');
const $muni    = document.getElementById('idmunicalc');
const $seccion = document.getElementById('seccion');

function resetSelect(sel, placeholder) {
  if (!sel) return;
  sel.innerHTML = `<option value="">${placeholder}</option>`;
}

$estado?.addEventListener('change', async () => {
  const v = $estado.value;
  resetSelect($muni, 'Cargando...');
  resetSelect($seccion, 'Selecciona una sección');
  await cargarMunicipios(v, '');
});

$muni?.addEventListener('change', async () => {
  const v = $muni.value;
  resetSelect($seccion, 'Cargando...');
  await cargarSecciones(v, '');
});

async function cargarMunicipios(idedo, selected = '') {
  if (!$muni) return;

  if (!idedo) {
    resetSelect($muni, 'Selecciona un municipio');
    resetSelect($seccion, 'Selecciona una sección');
    return;
  }

  $muni.innerHTML = '<option value="">Cargando...</option>';
  try {
    const res = await fetch(API_MUNICIPIOS_URL, {
      method: 'POST',
      headers: { 'Content-Type':'application/x-www-form-urlencoded' },
      body: 'idedo=' + encodeURIComponent(idedo)
    });
    const payload = await res.json();
    const rows = normalizeRows(payload);

    $muni.innerHTML = '<option value="">Selecciona un municipio</option>';
    rows.forEach(m => {
      const value = m.municalc ?? m.mun_code ?? '';
      const text  = m.mun_name ?? value;
      $muni.insertAdjacentHTML('beforeend',
        `<option value="${value}" ${String(value) === String(selected) ? 'selected' : ''}>${text}</option>`
      );
    });

    // Si viene selected, normalmente después cargas secciones desde quien llama
  } catch {
    $muni.innerHTML = '<option value="">Error al cargar</option>';
  }
}

async function cargarSecciones(idmunicalc, selected = '') {
  if (!$seccion) return;

  if (!idmunicalc) {
    resetSelect($seccion, 'Selecciona una sección');
    return;
  }

  $seccion.innerHTML = '<option value="">Cargando...</option>';
  try {
    const res = await fetch(API_SECCIONES_URL, {
      method: 'POST',
      headers: { 'Content-Type':'application/x-www-form-urlencoded' },
      body: 'idmunicalc=' + encodeURIComponent(idmunicalc)
    });
    const payload = await res.json();
    const rows = normalizeRows(payload);

    $seccion.innerHTML = '<option value="">Selecciona una sección</option>';
    rows.forEach(s => {
      const sec = s.seccion ?? s;
      $seccion.insertAdjacentHTML('beforeend',
        `<option value="${sec}" ${String(sec) === String(selected) ? 'selected' : ''}>${sec}</option>`
      );
    });
  } catch {
    $seccion.innerHTML = '<option value="">Error al cargar</option>';
  }
}

async function setDomicilioCascade(data) {
  const edo  = data?.estado   || '';
  const muni = data?.municalc || '';
  const sec  = data?.seccion  || '';

  if (edo) {
    setFieldByName('idedo', edo);
    await cargarMunicipios(edo, muni || '');
  }
  if (muni) {
    setFieldByName('idmunicalc', muni);
    await cargarSecciones(muni, sec || '');
  }
  if (sec) setFieldByName('seccion', sec);
}

// ===== Uppercase + dígitos =====
document.querySelectorAll('.text-uppercase').forEach(el => {
  el.addEventListener('input', () => toUpperNoAccents(el));
});

['cel','telpart','teloficina','cp','tel_proponente'].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener('input', () => {
    el.value = el.value.replace(/\D+/g,'').slice(0, id === 'cp' ? 5 : 10);
  });
});

// ===== Tabs (hash) =====
(function initTabsHash() {
  const tablist = document.getElementById('myTab');
  const panels  = document.getElementById('tabPanels');
  if (!tablist || !panels) return;

  const btns  = [...tablist.querySelectorAll('.nav-link[data-tab]')];
  const panes = [...panels.querySelectorAll('.tab-panel[data-tab]')];

  function activate(tab) {
    btns.forEach(b => {
      const on = b.dataset.tab === tab;
      b.classList.toggle('active', on);
      b.setAttribute('aria-selected', on ? 'true' : 'false');
    });
    panes.forEach(p => { p.hidden = (p.dataset.tab !== tab); });
  }

  const fromHash = location.hash.replace('#','');
  if (fromHash && panes.some(p => p.dataset.tab === fromHash)) activate(fromHash);

  btns.forEach(b => b.addEventListener('click', () => {
    const tab = b.dataset.tab;
    history.replaceState(null, '', '#' + tab);
    activate(tab);
  }));
})();

// ===== Resumen dinámico =====
function updateResumen() {
  const get = id => document.getElementById(id);
  const setTxt = (id, v) => { const el = get(id); if (el) el.textContent = v || '—'; };

  const fechaBonita = (d, m, y) => (!d || !m || !y) ? '' : `${String(d).padStart(2,'0')}/${String(m).padStart(2,'0')}/${y}`;
  const mapSexo = v => v === 'M' ? 'Hombre' : v === 'F' ? 'Mujer' : '';
  const selText = el => el ? (el.options[el.selectedIndex]?.textContent.trim() || '') : '';

  const nombreFull = [get('paterno')?.value, get('materno')?.value, get('nombre')?.value]
    .map(v => (v || '').toUpperCase()).filter(Boolean).join(' ');

  setTxt('sum-nombre', nombreFull);
  setTxt('sum-fecha', fechaBonita(get('dianac')?.value, get('mesnac')?.value, get('yearnac')?.value));
  setTxt('sum-sexo', mapSexo(get('genero')?.value));
  setTxt('sum-clave', get('clave_elector')?.value);
  setTxt('sum-estado', selText(get('idedo')));
  setTxt('sum-muni', selText(get('idmunicalc')));
  setTxt('sum-cel', get('cel')?.value);
  setTxt('sum-email', get('email')?.value);
}

[
  'paterno','materno','nombre','dianac','mesnac','yearnac',
  'genero','clave_elector','idedo','idmunicalc','seccion','cel','email'
].forEach(id => {
  const el = document.getElementById(id);
  if (!el) return;
  el.addEventListener('input', updateResumen);
  el.addEventListener('change', updateResumen);
});

// ===== CP (TomSelect) + reset limpio =====
function resetPorCP() {
  // Limpia MUNICIPIO/SECCION/COLONIA al cambiar o borrar CP
  const muni = document.getElementById('idmunicalc');
  const secc = document.getElementById('seccion');
  const col  = document.getElementById('colonia');

  if (muni) muni.innerHTML = '<option value="">Selecciona un municipio</option>';
  if (secc) secc.innerHTML = '<option value="">Selecciona una sección</option>';

  if (col) {
    if (col.tagName === 'SELECT') col.innerHTML = '<option value="">Selecciona colonia</option>';
    else col.value = '';
  }

  unlockMunicipio();
}

async function aplicarCP(cp) {
  if (!cp || String(cp).length !== 5) return;

  const res = await fetch(API_CP_URL, {
    method: 'POST',
    headers: { 'Content-Type':'application/x-www-form-urlencoded' },
    body: 'cp=' + encodeURIComponent(cp)
  });

  const payload = await res.json();
  if (!payload.ok) return;

  const d = payload.data;
  if (!d?.found) return;

  // 1) Estado
  setFieldByName('idedo', d.estado);

  // 2) Municipios del estado + seleccionar el del CP
  await cargarMunicipios(d.estado, d.municipio);

  // fuerza el value por si el select no quedó seleccionado por algún motivo
  setFieldByName('idmunicalc', d.municipio);

  lockMunicipio(d.municipio);

  // 3) IMPORTANTÍSIMO: cargar secciones del municipio del CP
  await cargarSecciones(d.municipio, ''); // o un selected si tu API de CP trae sección default

  // 4) Colonias
  const selCol = document.getElementById('colonia');
  if (selCol && Array.isArray(d.colonias)) {
    if (selCol.tagName === 'SELECT') {
      selCol.innerHTML = '<option value="">Selecciona colonia</option>';
      d.colonias.forEach(c => selCol.insertAdjacentHTML('beforeend', `<option value="${c}">${c}</option>`));
    } else {
      selCol.value = (d.colonias.length === 1) ? d.colonias[0] : '';
    }
  }

  // 5) Si el municipio debe quedar fijo por CP, se bloquea aquí
  if (LOCK_MUNI_BY_CP) lockMunicipioByCP(true);

  if (typeof updateResumen === 'function') updateResumen();
}

function lockMunicipio(val) {
  const muni = document.getElementById('idmunicalc');
  const hid  = document.getElementById('idmunicalc_locked');
  if (!muni || !hid) return;

  muni.value = String(val || '');
  hid.value  = String(val || '');

  muni.disabled = true;

  // evita validación nativa sobre un campo que el usuario no puede cambiar
  muni.removeAttribute('required');
}

function unlockMunicipio() {
  const muni = document.getElementById('idmunicalc');
  const hid  = document.getElementById('idmunicalc_locked');
  if (!muni || !hid) return;

  muni.disabled = false;
  muni.setAttribute('required', 'required'); // si quieres exigirlo cuando está editable
  hid.value = '';
}

function initTomSelectCP() {
  const cpEl = document.getElementById('cp');
  if (!cpEl || typeof TomSelect === 'undefined') return;

  const ts = new TomSelect(cpEl, {
    valueField: 'value',
    labelField: 'text',
    searchField: ['value', 'text'],
    maxItems: 1,
    create: false,
    preload: false,
    placeholder: 'Escribe CP...',
    closeAfterSelect: true,
    loadThrottle: 400,

    render: {
      option: (item, escape) => `<div>${escape(item.text)}</div>`,
      item: (item, escape) => `<div>${escape(item.value)}</div>`
    },

    load: async function(query, callback) {
      try {
        const q = String(query || '').replace(/\D+/g,'').slice(0,5);
        if (q.length < 2) return callback();

        const url = `${API_SEARCH_CP_URL}?q=${encodeURIComponent(q)}`;
        const res = await fetch(url, { method: 'GET' });
        const data = await res.json();
        callback(Array.isArray(data) ? data : []);
      } catch (e) {
        console.error(e);
        callback();
      }
    },

    onChange: async function(value) {
      if (!value) {
        resetPorCP();
        return;
      }
      try {
        await aplicarCP(value);
      } catch (e) {
        console.error(e);
      }
    }
  });

  // Si viene precargado (editar)
  const initial = String(cpEl.value || '').replace(/\D+/g,'').slice(0,5);
  if (initial.length === 5) {
    ts.setValue(initial, true); // silent
    aplicarCP(initial).catch(console.error);
  }
}

// ===== Select2 Proponente (AJAX) =====
function initProponenteSelect2() {
  if (!window.jQuery || !window.$ || !$.fn.select2) return;

  const $sel = $('#estrucasoc');
  if (!$sel.length) return;

  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy');

  $sel.select2({
    placeholder: 'Escribe para buscar…',
    allowClear: true,
    width: '100%',
    minimumInputLength: 2,
    ajax: {
      url: API_SEARCH_PROP_URL,
      dataType: 'json',
      delay: 250,
      cache: true,
      data: function (params) {
        return {
          scope: PROP_SCOPE,
          q: (params.term || '').trim(),
          estado: ($('#idedo').val() || '').trim(),
          limit: 20
        };
      },
      processResults: function (data) {
        const rows = Array.isArray(data) ? data : [];
        return {
          results: rows.map(r => ({ id: r.label, text: r.label }))
        };
      }
    }
  });

  // Si cambia estado, limpias selección
  $('#idedo').on('change', function () {
    $sel.val(null).trigger('change');
  });
}

// ===== Llenar datos existentes (EDIT) =====
document.addEventListener('DOMContentLoaded', async () => {
  initFotoUI();
  initFotoIneUI();
  toggleCopiaIneUI();
  document.getElementById('copia_credelec')?.addEventListener('change', toggleCopiaIneUI);

  initTomSelectCP();
  initProponenteSelect2();

  const id = document.getElementById('registro_id')?.value;
  if (!id) {
    updateResumen();
    return;
  }

  try {
    const res = await fetch(API_GET_REGISTRO_URL, {
      method: 'POST',
      headers: { 'Content-Type':'application/x-www-form-urlencoded' },
      body: 'id=' + encodeURIComponent(id)
    });
    const resp = await res.json();
    if (resp.ok && resp.data) {
      await llenarFormulario(resp.data);
    }
  } catch (err) {
    console.error(err);
    alert('Error al cargar datos');
  } finally {
    updateResumen();
  }
});

async function llenarFormulario(data) {
  if (!data || typeof data !== 'object') return;

  for (const fieldKey in data) {
    if (!Object.prototype.hasOwnProperty.call(data, fieldKey)) continue;

    const mapTo = FIELD_MAP[fieldKey];
    const val   = data[fieldKey];
    if (!mapTo) continue;

    // fechanac -> 3 selects
    if (Array.isArray(mapTo) && fieldKey === 'fechanac') {
      const parsed = parseFechaNac(val);
      if (parsed) {
        const elDia  = document.getElementById('dianac');
        const elMes  = document.getElementById('mesnac');
        const elYear = document.getElementById('yearnac');
        if (elDia)  elDia.value  = parsed.dd;
        if (elMes)  elMes.value  = parsed.mm;
        if (elYear) elYear.value = parsed.yyyy;
      }
      continue;
    }

    if (typeof mapTo === 'string') setFieldByName(mapTo, val);
  }

  await setDomicilioCascade(data);
  setAfinidad(data);

  setFotoPreviewFromData(data);
  setInePreviewFromData(data);
  toggleCopiaIneUI();

  updateResumen();
}

// ===== SUBMIT (ÚNICO) =====
const form = document.getElementById('formCrear');
let submitting = false;

if (form) {
  form.noValidate = true;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    resetInvalid();

    // Validación nativa controlada + focus en tab correcto
    if (!form.checkValidity()) {
      focusFirstInvalid(form);
      return;
    }

    if (submitting) return;
    submitting = true;

    const submitBtn = form.querySelector('[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    // Detecta edición
    const idValue =
      (document.getElementById('registro_id')?.value ?? '').trim() ||
      (form.querySelector('[name="id"]')?.value ?? '').trim();

    const isEdit = (idValue !== '' && idValue !== '0');

    try {
      const fd = new FormData(form);

      // 🔒 Forzar que SIEMPRE viaje el archivo INE si el usuario lo eligió
      const selIne = document.getElementById('copia_credelec');
      if (selIne) fd.set('copia_credelec', (selIne.value || '').trim().toUpperCase());

      const ineInput = document.getElementById('foto_ine');
      if (ineInput && ineInput.files && ineInput.files[0]) {
        fd.set('foto_ine', ineInput.files[0]); // ✅ aunque el input no esté dentro del form
      }

      // (opcional) fuerza id para que no caiga en INSERT por error
      if (idValue) fd.set('id', idValue);

      // ===== Sync UI -> Hidden (tipo_form / num_ruta) =====
      const tipoUI = document.getElementById('tipo_form_ui');
      const tipoH  = document.getElementById('tipo_form');
      if (tipoUI && tipoH) {
        tipoH.value = (tipoUI.value || '').trim();
        fd.set('tipo_form', tipoH.value);
      }

      const rutaUI = document.getElementById('num_ruta_ui');
      const rutaH  = document.getElementById('num_ruta');
      if (rutaUI && rutaH) {
        rutaH.value = (rutaUI.value || '').trim();
        fd.set('num_ruta', rutaH.value);
      }

      // Si municipio está disabled, manda el hidden (si aplica)
      const muni = document.getElementById('idmunicalc');
      const hid  = document.getElementById('idmunicalc_locked');
      if (muni && muni.disabled && hid) {
        hid.value = muni.value || '';
        if (hid.value) fd.set('idmunicalc_locked', hid.value);
      }

      const r = await fetch(SAVE_URL, {
        method: 'POST',
        body: fd,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        }
      });

      // Lee texto SIEMPRE para poder ver HTML/fatal error
      const raw = await r.text();

      // Intenta JSON si se puede
      let res;
      try {
        res = raw ? JSON.parse(raw) : null;
      } catch {
        res = null;
      }

      if (!r.ok) {
        console.error('HTTP', r.status, raw);
        await Swal.fire({
          icon: 'error',
          title: `Servidor ${r.status}`,
          text: (res && res.msg) ? res.msg : (raw ? raw.slice(0, 250) : 'Respuesta vacía (posible fatal error PHP)')
        });
        return;
      }

      if (!res) {
        console.error('Respuesta no JSON:', raw);
        await Swal.fire({ icon:'error', title:'Error', text:'El servidor no devolvió JSON válido.' });
        return;
      }

      // tu flujo normal
      if (!res.ok) {
        if (Array.isArray(res.faltan) && res.faltan.length) {
          markInvalid(res.faltan);
          focusFirstMarkedInvalid(form); // ✅ abre tab + focus
        }
        await Swal.fire({ icon:'warning', title:'Atención', text: res.msg || 'No se pudo guardar' });
        return;
      }

      await Swal.fire({
        icon: 'success',
        title: isEdit ? 'Actualizado' : 'Guardado',
        timer: 1200,
        showConfirmButton: false
      });

      // ✅ Si es UPDATE: siempre regresa al DataTable
      if (isEdit) {
        window.location.assign(REDIRECT_URL); // tu datatable
        return;
      }

      // ✅ Si es INSERT: respetas tu modo captura consecutiva
      // ✅ INSERT: se queda para captura consecutiva
      // ✅ UPDATE: regresa al listado/datatable
      if (isEdit) {
        window.location.assign(REDIRECT_URL);
        return;
      }

      // si no rediriges en INSERT, entonces reset consecutivo
      await resetForNextInsert();

      // ✅ INSERT: se queda para captura consecutiva
      // ✅ UPDATE: regresa al listado/datatable
      if (isEdit) {
        window.location.assign(REDIRECT_URL);
        return;
      }

      // Captura consecutiva (solo si INSERT)
      if (!isEdit) await resetForNextInsert();

    } catch (err) {
      console.error(err);
      await Swal.fire({ icon:'error', title:'Error', text:'Fallo de red o servidor' });
    } finally {
      submitting = false;
      if (submitBtn) submitBtn.disabled = false;
      updateResumen();
    }
  });
}

async function resetForNextInsert() {
  const keep = {};
  PRESERVE_AFTER_INSERT.forEach(id => {
    const el = document.getElementById(id);
    if (el) keep[id] = el.value;
  });

  form.reset();
  resetInvalid();
  resetFotoPreview();

  // limpiar ids para forzar INSERT
  const elRegistroId = document.getElementById('registro_id');
  if (elRegistroId) elRegistroId.value = '';

  const elId = form.querySelector('[name="id"]');
  if (elId) elId.value = '0';

  // limpiar URL si venía con ?id=
  const cleanUrl = location.pathname + location.hash;
  history.replaceState(null, '', cleanUrl);

  // restaurar preservados
  if (keep.idedo) {
    setFieldByName('idedo', keep.idedo);
    await cargarMunicipios(keep.idedo, keep.idmunicalc || '');
  }
  if (keep.idmunicalc) {
    setFieldByName('idmunicalc', keep.idmunicalc);
    await cargarSecciones(keep.idmunicalc, keep.seccion || '');
  }
  if (keep.seccion) setFieldByName('seccion', keep.seccion);
  if (keep.estadodom) setFieldByName('estadodom', keep.estadodom);

  // limpia "otro" si no está en "otro"
  const afin = document.getElementById('afinidad')?.value;
  if (String(afin).toLowerCase() !== 'otro') {
    const otro = document.getElementById('otroPartido') || document.querySelector('[name="otro"]');
    if (otro) otro.value = '';
  }

  toggleCopiaIneUI();
  updateResumen();
}

document.getElementById('colonia')?.addEventListener('change', async () => {
  const muni = document.getElementById('idmunicalc')?.value || '';
  if (muni) await cargarSecciones(muni, document.getElementById('seccion')?.value || '');
});