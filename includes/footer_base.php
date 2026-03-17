</div> <!-- Cierra main-container -->

<footer class="text-center mt-auto py-3 text-light bg-dark">
  <p class="mb-0">&copy; <?= date('Y') ?> Todos los derechos reservados. - v1.3.0</p>
</footer>

<!-- ================== JS BASE ================== -->
<script src="<?= asset('assets/js/jquery-3.6.0.min.js')?>"></script>
<script src="<?= asset('assets/js/bootstrap.bundle.min.js')?>"></script>
<script src="<?= asset('assets/js/security.js') ?>"></script>

<!-- ================== JS PERSONALIZADOS ================== -->
<?php if (!empty($customScripts)) echo implode("\n", $customScripts); ?>

<script>
// =============================================
// ⚙️ Sidebar colapsable + ajuste de DataTables
// =============================================
function adjustDataTables(){
  if (window.jQuery && $.fn.dataTable) {
    $('.dataTable').each(function(){
      try { $(this).DataTable().columns.adjust().draw(false); } catch(e){}
    });
  }
}

// --- Colapsar/expandir sidebar en escritorio ---
document.getElementById('btnCollapseSidebar')?.addEventListener('click', function () {
  document.querySelector('.sidebar')?.classList.toggle('collapsed');
  document.body.classList.toggle('with-sidebar-collapsed');
  const sb = document.querySelector('.sidebar');
  if (sb){
    const onEnd = () => { adjustDataTables(); sb.removeEventListener('transitionend', onEnd); };
    sb.addEventListener('transitionend', onEnd);
    setTimeout(adjustDataTables, 250);
  }
});

// --- Cuando se cierra el offcanvas móvil ---
document.getElementById('offcanvasMenu')?.addEventListener('hidden.bs.offcanvas', adjustDataTables);

// ==========================================================
// 🧠 Sidebar avanzado: hover inteligente cuando está colapsado
// ==========================================================
(function () {
  const MQ = 768; // breakpoint Bootstrap md
  const isDesktop = () => window.innerWidth >= MQ;
  const isCollapsed = () => document.body.classList.contains('with-sidebar-collapsed');

  const setOpen = (dd, open) => {
    const toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
    if (!toggle) return;
    dd.classList.toggle('show', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  };

  const enableHoverFlyout = () => {
    const dds = document.querySelectorAll('.sidebar .dropdown');
    dds.forEach(dd => {
      if (dd.__hoverBound) return;
      dd.__hoverBound = true;

      dd.addEventListener('mouseenter', () => {
        if (isDesktop() && isCollapsed()) setOpen(dd, true);
      });
      dd.addEventListener('mouseleave', () => {
        if (isDesktop() && isCollapsed()) setOpen(dd, false);
      });

      const toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
      if (toggle) {
        toggle.addEventListener('click', (e) => {
          if (isDesktop() && isCollapsed()) {
            e.preventDefault();
            setOpen(dd, !dd.classList.contains('show'));
          }
        });
      }
    });
  };

  const closeAll = () => {
    document.querySelectorAll('.sidebar .dropdown.show')
      .forEach(dd => setOpen(dd, false));
  };

  const onChange = () => {
    if (isDesktop() && isCollapsed()) {
      enableHoverFlyout();
    } else {
      closeAll();
    }
  };

  window.addEventListener('resize', onChange);
  document.addEventListener('DOMContentLoaded', () => {
    enableHoverFlyout();
    onChange();
  });
})();

// ==========================================================
// ♿️ Accesibilidad: aria-label en los enlaces del sidebar
// ==========================================================
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.sidebar .nav-link').forEach(a => {
    if (!a.hasAttribute('aria-label')) {
      const span = a.querySelector('span');
      const text = span ? span.textContent.trim() : a.textContent.trim();
      if (text) a.setAttribute('aria-label', text);
    }
  });
});
</script>

</body>
</html>
