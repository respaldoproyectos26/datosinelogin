// assets/js/tabs.js
document.addEventListener('DOMContentLoaded', () => {
  const tabButtons = document.querySelectorAll('.nav-link[data-tab]');
  const panels     = document.querySelectorAll('#tabPanels .tab-panel[data-tab]');

  if (!tabButtons.length || !panels.length) return;

  function activate(tabName) {
    // botones
    tabButtons.forEach(btn => {
      const isActive = btn.getAttribute('data-tab') === tabName;
      btn.classList.toggle('active', isActive);
      btn.setAttribute('aria-selected', isActive ? 'true' : 'false');
    });

    // panels
    panels.forEach(p => {
      const isTarget = p.getAttribute('data-tab') === tabName;
      if (isTarget) p.removeAttribute('hidden');
      else p.setAttribute('hidden', '');
    });
  }

  // click handlers
  tabButtons.forEach(btn => {
    btn.addEventListener('click', () => activate(btn.getAttribute('data-tab')));
  });

  // inicial: el que venga marcado "active" o el primero
  const initial = document.querySelector('.nav-link[data-tab].active')?.getAttribute('data-tab')
               || tabButtons[0].getAttribute('data-tab');

  activate(initial);
});
