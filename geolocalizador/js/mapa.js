// (Opcional) guardafallas: evita doble carga del archivo
if (window.__MAPA_JS_LOADED__) {
  console.warn('mapa.js ya estaba cargado');
}
window.__MAPA_JS_LOADED__ = true;

// ======= Performance / Etiquetas =======
const LABELS_MIN_ZOOM = 13;          // si algún día quieres pintar muchas etiquetas, exige este zoom
const SHOW_LABELS_WHEN_SHOW_ALL = false; // ← pediste NO mostrar etiquetas en "Mostrar todas"

// ================== Config & estado global ==================
const GEOJSON_BASE = window.GEOJSON_BASE || '../../geojson';

let _fetchCtrl = null;
let _idleTimer = null;

const ZOOM_POINTS_MIN = 12;   // <12 usa aggregates

// ====== Estado actual ======
const ENTIDAD_ACTUAL = 24;                 // San Luis Potosí en tu GeoJSON
const STATE_NAME = 'San Luis Potosí';
const DEFAULT_CENTER = { lat: 22.1565, lng: -100.9855 }; // centro SLP aprox
const DEFAULT_ZOOM = 7;

let map;
let markers = [];
let clusterer;
let __seccionesCargadas = false;

let selectedInfoWin = null;
let legendDiv = null;

// Etiquetas de secciones (usamos google.maps.Marker clásico con icono transparente + label)
let sectionLabels = [];

// Icono transparente 1x1 para “label-only”
const BLANK_ICON_DATAURL =
  'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==';

// ================== Utilidades ==================
function stripAccents(str) {
  return (str || '').toString().normalize('NFD').replace(/\p{Diacritic}/gu, '').toUpperCase().trim();
}
function sameMun(a, b) { return stripAccents(a) === stripAccents(b); }

function zoomToBounds(bounds, centerFallback) {
  if (!bounds || bounds.isEmpty()) {
    if (centerFallback) { map.setCenter(centerFallback); map.setZoom(14); }
    return;
  }
  requestAnimationFrame(() => {
    requestAnimationFrame(() => map.fitBounds(bounds, 40));
  });
}

async function ensureSeccionesCargadas() {
  if (__seccionesCargadas) return;
  await cargarEstadoUnaVez();
  // espera corta si el fetch aún no ha terminado
  const start = Date.now();
  while (!__seccionesCargadas && Date.now() - start < 3000) {
    await new Promise(r => setTimeout(r, 50));
  }
}

function getFeatureCenter(feature) {
  const bounds = new google.maps.LatLngBounds();
  feature.getGeometry().forEachLatLng(ll => bounds.extend(ll));
  return bounds.getCenter();
}

// ================== Etiquetas (Markers con label) ==================
function clearSectionLabels() {
  sectionLabels.forEach(m => m.setMap(null));
  sectionLabels = [];
}

function makeSectionLabel(feature, isSelected = false) {
  const pos = getFeatureCenter(feature);
  const sec = (feature.getProperty('SECCION') || '').toString();
  const munName = (feature.getProperty('NOM_MUN') || '').toString();
  const munCode = (feature.getProperty('MUNICIPIO') ?? '').toString();
  const muni = munName || munCode || '';

  const marker = new google.maps.Marker({
    map,
    position: pos,
    icon: { url: BLANK_ICON_DATAURL, size: new google.maps.Size(1,1), anchor: new google.maps.Point(0,0) },
    label: {
      text: muni ? `${sec} · ${muni}` : `${sec}`,
      color: isSelected ? '#0b57d0' : '#1f2937',
      fontWeight: isSelected ? '700' : '600'
    },
    clickable: false,
    zIndex: isSelected ? 999 : 1
  });

  sectionLabels.push(marker);
  return marker;
}


/** Regenera etiquetas para features encendidos (__on = true).
 *  Si secSelected se pasa, sólo esa será "selected". Si showAll=false, sólo etiqueta la seleccionada.
 */
function refreshSectionLabels({ showAll = false, secSelected = null } = {}) {
  clearSectionLabels();
  if (!__seccionesCargadas) return;

  // si viene una sección seleccionada → etiqueta SOLO esa
  if (secSelected) {
    map.data.forEach(ft => {
      if (ft.getProperty('__on') === true) {
        const ftSec = (ft.getProperty('SECCION') || '').toString();
        if (ftSec === secSelected) makeSectionLabel(ft, true);
      }
    });
    return;
  }

  // Si NO hay sección seleccionada:
  //  - En "mostrar todas": NO pintar (por rendimiento)
  //  - Si quisieras permitir, respeta threshold de zoom
  if (showAll === true) {
    if (!SHOW_LABELS_WHEN_SHOW_ALL) return;
    if ((map.getZoom() || 0) < LABELS_MIN_ZOOM) return;
  }

  // (opcional) etiqueta visibles cuando no hay sección (y no estamos en "mostrar todas")
  map.data.forEach(ft => {
    if (ft.getProperty('__on') === true) {
      makeSectionLabel(ft, false);
    }
  });
}

// ================== INIT ==================
window.initMap = function () {
  map = new google.maps.Map(document.getElementById("mapa"), {
  center: DEFAULT_CENTER,
  zoom: DEFAULT_ZOOM,
  });

  /*
  map.addListener('zoom_changed', () => {
    refreshSectionLabels({ showAll: true });
  });
  */

  ensureLegend(map);
  cargarEstadoUnaVez();

  // Marcadores: inicial + onIdle
  mostrarPorFiltros();
  map.addListener('idle', () => scheduleMostrarPorFiltros());

  // Listeners de filtros (SIN jQuery)
  const selEstado = document.getElementById('filtro_estado');
  const selMun = document.getElementById('filtro_municipio');
  const selSec = document.getElementById('filtro_seccion');

  // (opcional) expón para depurar en consola
  window.selEstado = selEstado;
  window.selMun = selMun;
  window.selSec = selSec;

  // Estado -> Municipios (desde tu API) y limpio mapa
  selEstado?.addEventListener('change', async () => {
    const idedo = selEstado.value;
    selMun.innerHTML = '<option value="">-- Todos los municipios --</option>';
    selSec.innerHTML = '<option value="">-- Todas las secciones --</option>';
    selSec.disabled = true;
    if (!idedo) {
      ocultarTodas();
      return;
    }
    try {
      const resp = await fetchCSRF('api/get_municipios.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: new URLSearchParams({ idedo })
      });
      if (!resp.ok) throw new Error('HTTP ' + resp.status);
      const data = await resp.json();
      selMun.innerHTML = '<option value="">-- Todos los municipios --</option>';
      data.forEach(m => {
        const opt = document.createElement('option');
        opt.value = String(m.mun_code);   // ← VALUE numérico
        opt.textContent = m.mun_name;     // ← texto visible
        selMun.appendChild(opt);
      });
      ocultarTodas();
    } catch (e) {
      console.error(e);
      alert('Hubo un error al cargar los municipios.');
    }
  });

  // Municipio -> Secciones (API + GeoJSON) y aplicar filtro de municipio
  selMun?.addEventListener('change', async () => {
    await ensureSeccionesCargadas();

    const idmunicalc = selMun.value;
    selSec.innerHTML = '<option value="">-- Todas las secciones --</option>';
    if (!idmunicalc) {
      selSec.disabled = true;
      ocultarTodas();
      return;
    }

    // Visual: filtra por municipio (sin sección) + llena desde GeoJSON
    aplicarFiltroMunicipioSeccion(idmunicalc, '');
    poblarSeccionesDesdeMapa(idmunicalc);
    selSec.disabled = false;

    const idedo = document.getElementById('filtro_estado')?.value || '24';

    // (Opcional) Refuerzo desde API
    try {
      const resp = await fetchCSRF('api/get_secciones.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
        body: new URLSearchParams({ idedo, idmunicalc })
      });
      if (resp.ok) {
        const data = await resp.json();
        const ya = new Set(Array.from(selSec.options).map(o => o.value));
        data.forEach(s => {
          const v = String(s.seccion);
          if (!ya.has(v) && v) {
            const opt = document.createElement('option');
            opt.value = v;
            opt.textContent = v;
            selSec.appendChild(opt);
          }
        });
      }
    } catch { }

    mostrarPorFiltros();
  });

  // Sección -> aplicar filtro de municipio+sección
  selSec?.addEventListener('change', async () => {
    await ensureSeccionesCargadas();

    const muni = selMun?.value || '';
    const sec = selSec?.value || '';
    aplicarFiltroMunicipioSeccion(muni, sec);
    mostrarPorFiltros();
  });

  // Categoría / Estatus -> marcadores
  document.getElementById('filtro_categoria')?.addEventListener('change', () => scheduleMostrarPorFiltros());
  document.getElementById('filtro_estatus')?.addEventListener('change', () => scheduleMostrarPorFiltros());

  if (selEstado) {
    selEstado.value = '24'; // coincide con lo que pides en markers.php
    selEstado.dispatchEvent(new Event('change'));
  }

  /*
  if (selEstado) {
  selEstado.value = '24';               // SLP = 24
  selEstado.dispatchEvent(new Event('change'));
}
  */
};

// ================== UI helpers (leyenda) ==================
function ensureLegend(map) {
  if (legendDiv) return;
  legendDiv = document.createElement('div');
  Object.assign(legendDiv.style, {
    background: 'rgba(255,255,255,0.95)',
    border: '1px solid #ccc',
    borderRadius: '8px',
    boxShadow: '0 1px 4px rgba(0,0,0,.3)',
    padding: '8px 12px',
    margin: '8px',
    font: '13px/1.4 Arial, sans-serif'
  });
  legendDiv.innerHTML = '<b>Sección:</b> — <small>(elige municipio y sección)</small>';
  map.controls[google.maps.ControlPosition.TOP_RIGHT].push(legendDiv);
}
function updateLegend(seccion, municipio) {
  if (!legendDiv) return;
  legendDiv.innerHTML = seccion
    ? `<b>Sección:</b> ${seccion} <span style="color:#666">· Mun:</span> ${municipio ?? '—'}`
    : (municipio ? `<b>Municipio:</b> ${municipio}` : '<b>Sección:</b> —');
}

// ================== Capa de secciones (GeoJSON) ==================
async function cargarEstadoUnaVez() {
  if (__seccionesCargadas) return;

  const selectSecc = document.getElementById('filtro_seccion');
  if (selectSecc) {
    selectSecc.innerHTML = '<option value="">Cargando secciones…</option>';
    selectSecc.disabled = true;
  }

  const urlEstado = `${GEOJSON_BASE}/secciones_sanluis_wgs84.json`;

  try {
    const res = await fetch(urlEstado, { credentials: 'same-origin' });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    map.data.forEach(f => map.data.remove(f));
    map.data.addGeoJson(data);

    // Por defecto: NO mostrar nada
    map.data.forEach(ft => {
      ft.setProperty('__on', false);
      ft.setProperty('__selected', false);
    });

    // *** Estilo controlado por flags __on / __selected ***
    map.data.setStyle((feature) => {
      const on = feature.getProperty('__on') === true;
      const selected = feature.getProperty('__selected') === true;

      if (!on) {
        return { strokeWeight: 0, fillOpacity: 0 };
      }
      if (selected) {
        return {
          strokeColor: '#0b57d0',
          strokeWeight: 3,
          fillColor: '#8ab4f8',
          fillOpacity: 0.35
        };
      }
      return {
        strokeColor: '#1a73e8',
        strokeWeight: 1,
        fillColor: '#8ab4f8',
        fillOpacity: 0.18
      };
    });

    __seccionesCargadas = true;

    // Enfoca al estado al terminar de cargar
    const stateBounds = new google.maps.LatLngBounds();
    map.data.forEach(ft => {
      ft.getGeometry().forEachLatLng(ll => stateBounds.extend(ll));
    });
    if (!document.getElementById('filtro_municipio')?.value &&
        !document.getElementById('filtro_seccion')?.value) {
      zoomToBounds(stateBounds, DEFAULT_CENTER);
    }

    // Hover (solo override del feature, NO revert global)
    google.maps.event.clearListeners(map.data, 'mouseover');
    google.maps.event.clearListeners(map.data, 'mouseout');

    map.data.addListener('mouseover', (e) => {
      if (e.feature.getProperty('__on') === true &&
        e.feature.getProperty('__selected') !== true) {
        map.data.overrideStyle(e.feature, { strokeWeight: 2, fillOpacity: 0.22 });
      }
    });

    map.data.addListener('mouseout', (e) => {
      map.data.overrideStyle(e.feature, null); // solo este feature
    });

    if (selectSecc) {
      selectSecc.innerHTML = '<option value="">-- Selecciona un municipio primero --</option>';
      selectSecc.disabled = true;
    }

  } catch (err) {
    console.error('No pude cargar el GeoJSON del estado:', err);
    if (selectSecc) {
      selectSecc.innerHTML = '<option value="">No se cargaron secciones</option>';
      selectSecc.disabled = true;
    }
  }
}

// Mostrar/Ocultar todas
function ocultarTodas() {
  if (!__seccionesCargadas) return;
  map.data.forEach(ft => {
    ft.setProperty('__on', false);
    ft.setProperty('__selected', false);
  });
  map.data.revertStyle();
  clearSectionLabels();
  if (selectedInfoWin) { selectedInfoWin.close(); selectedInfoWin = null; }
  updateLegend(null, null);
}

function mostrarTodas() {
  if (!__seccionesCargadas) return;
  const bounds = new google.maps.LatLngBounds();
  map.data.forEach(ft => {
    ft.setProperty('__on', true);
    ft.setProperty('__selected', false);
    ft.getGeometry().forEachLatLng(ll => bounds.extend(ll));
  });
  map.data.revertStyle();
  zoomToBounds(bounds, null);
  refreshSectionLabels({ showAll: true });
  updateLegend(null, STATE_NAME);
}

// Rellena <select id="filtro_seccion"> desde lo cargado (GeoJSON)
function poblarSeccionesDesdeMapa(muniValue) {
  const select = document.getElementById('filtro_seccion');
  if (!select) return;

  const v = (muniValue || '').toString().trim();
  const isNum = /^\d+$/.test(v);
  const code = isNum ? +v : null;

  const set = new Set();
  map.data.forEach(ft => {
    const ftMun = +ft.getProperty('MUNICIPIO');
    if (v) {
      if (!isNum) return;           // si te llega texto, aquí podrías mapearlo
      if (ftMun !== code) return;
    }
    const s = ft.getProperty('SECCION');
    if (s !== undefined && s !== null && s !== '') set.add(String(s));
  });

  const lista = Array.from(set).sort((a, b) => (+a) - (+b));
  select.innerHTML = '<option value="">-- Todas las secciones --</option>';
  for (const s of lista) {
    const opt = document.createElement('option');
    opt.value = s;
    opt.textContent = s;
    select.appendChild(opt);
  }
  select.disabled = (lista.length === 0);
}

// Aplica filtro municipio/sección (enciende __on, marca __selected, etiqueta y centra)
function aplicarFiltroMunicipioSeccion(muniValue, seccionValue) {
  if (!__seccionesCargadas) return;

  const v = (muniValue || '').toString().trim();
  const isNum = /^\d+$/.test(v);
  const muniCode = isNum ? +v : null;
  const sec = (seccionValue || '').toString().trim();

  // apaga todo
  map.data.forEach(ft => { ft.setProperty('__on', false); ft.setProperty('__selected', false); });

  const bounds = new google.maps.LatLngBounds();
  let centerFallback = null;
  let matches = 0;

  map.data.forEach(ft => {
    const ftMun = +ft.getProperty('MUNICIPIO');
    const ftSec = (ft.getProperty('SECCION') || '').toString();
    let on = false;

    if (sec) {
      const muniOk = !isNum || (ftMun === muniCode); // si hay muni, filtra; si no, ignora
      on = (ftSec === sec) && muniOk;
    } else {
      on = !isNum || (ftMun === muniCode);
    }

    if (on) {
      ft.setProperty('__on', true);
      matches++;
      ft.getGeometry().forEachLatLng(ll => bounds.extend(ll));
      if (sec && !centerFallback && ftSec === sec) centerFallback = getFeatureCenter(ft);
    }
  });

  // si no hubo match por (muni+sec), intenta sólo por sección
  if (sec && matches === 0) {
    map.data.forEach(ft => {
      const ftSec = (ft.getProperty('SECCION') || '').toString();
      if (ftSec === sec) {
        ft.setProperty('__on', true);
        matches++;
        ft.getGeometry().forEachLatLng(ll => bounds.extend(ll));
        if (!centerFallback) centerFallback = getFeatureCenter(ft);
      }
    });
  }

  // marca seleccionada
  if (sec) {
    map.data.forEach(ft => {
      const ftSec = (ft.getProperty('SECCION') || '').toString();
      if (ft.getProperty('__on') && ftSec === sec) ft.setProperty('__selected', true);
    });
  }

  map.data.revertStyle();
  requestAnimationFrame(() => {
    if (sec) refreshSectionLabels({ showAll: false, secSelected: sec });
    else     refreshSectionLabels({ showAll: true });
    zoomToBounds(bounds, centerFallback || DEFAULT_CENTER);
  });

  // leyenda
  updateLegend(sec || null, v || null);
  if (!sec && selectedInfoWin) { selectedInfoWin.close(); selectedInfoWin = null; }
}

// ================== Marcadores / API ==================
function getBoundsParams() {
  if (!map || !map.getBounds) return {};
  const b = map.getBounds();
  if (!b) return {};
  const ne = b.getNorthEast();
  const sw = b.getSouthWest();
  const round6 = (n) => +Number(n).toFixed(6);
  return {
    nelat: round6(ne.lat()),
    nelng: round6(ne.lng()),
    swlat: round6(sw.lat()),
    swlng: round6(sw.lng()),
    entidad: ENTIDAD_ACTUAL
  };
}

let __markersTimer = null;
let __markersAbort = null;
let __lastKey = '';

function scheduleMostrarPorFiltros() {
  if (__markersTimer) clearTimeout(__markersTimer);
  __markersTimer = setTimeout(() => mostrarPorFiltros(), 250);
}

async function mostrarPorFiltros() {
  if (!map) return;

  const zoom = map.getZoom() || 8;
  const useAgg = zoom < 13; // umbral (ajústalo)

  const categoria = document.getElementById('filtro_categoria')?.value || '';
  const estatus   = document.getElementById('filtro_estatus')?.value || '';

  const params = new URLSearchParams({
    categoria, estatus,
    zoom: String(zoom),
    ...getBoundsParams()
  });

  const bounds = map.getBounds();
  if (!bounds) return; // aún no listo

  const ne = bounds.getNorthEast();
  const sw = bounds.getSouthWest();

  const endpoint = useAgg ? 'api/markers_agg.php' : 'api/markers.php';
  const url = endpoint + '?' + params.toString();

  // evita repetición
  const key = endpoint + '|' + params.toString();
  if (key === __lastKey) return;
  __lastKey = key;

  // aborta request anterior
  if (__markersAbort) __markersAbort.abort();
  __markersAbort = new AbortController();

  try {
    const resp = await fetch(url, { signal: __markersAbort.signal, credentials:'same-origin' });
    if (!resp.ok) {
      const t = await resp.text().catch(()=> '');
      console.error('markers error', resp.status, t);
      return;
    }
    const rows = await resp.json();

    // limpia previos
    if (clusterer) { clusterer.setMap(null); clusterer = null; }
    markers.forEach(m => m.setMap(null));
    markers = [];

    if (useAgg) {
      // rows: {lat,lng,count}
      rows.forEach(p => {
        if (!p.lat || !p.lng) return;
        const c = Number(p.count || 0);

        const m = new google.maps.Marker({
          map,
          position: { lat: +p.lat, lng: +p.lng },
          label: { text: String(c), fontWeight: '700' }
        });

        // click = acercar
        m.addListener('click', () => {
          map.setCenter(m.getPosition());
          map.setZoom(Math.min((map.getZoom() || 8) + 2, 18));
        });

        markers.push(m);
      });

      // NO uses clusterer aquí (ya vienen agregados)
      return;
    }

    function escHtml(s){
      return String(s ?? '').replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
      }[m]));
    }

    // NO agg: rows individuales (tu markers.php)
    const info = new google.maps.InfoWindow();
    rows.forEach(p => {
      if (!p.lat || !p.lng) return;
      const marker = new google.maps.Marker({
        map,
        position: { lat: +p.lat, lng: +p.lng },
        title: p.nombre || p.direccion || `ID ${p.id}`
      });
      marker.addListener('click', () => {
        info.setContent(
          `<b>${escHtml(p.nombre || '(sin nombre)')}</b>` +
          `<br><b>Estructura:</b> ${escHtml(p.figura || '—')}` +
          `<br><b>Tel:</b> ${escHtml(p.telefono || '—')}` +
          `<br><b>Estatus:</b> ${escHtml(p.estatus || '—')}` +
          `<br><small>${escHtml(p.direccion || '')}</small>`
        );
        info.open({ map, anchor: marker });
      });
      markers.push(marker);
    });

    if (markers.length) {
      clusterer = new markerClusterer.MarkerClusterer({ map, markers });
    }
  } catch (e) {
    if (e?.name === 'AbortError') return;
    console.error('mostrarPorFiltros error', e);
  }
}
