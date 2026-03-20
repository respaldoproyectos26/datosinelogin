</div> <!-- Cierra main-container -->

<footer class="text-center">
  <p class="mb-0">&copy; <?= date('Y') ?> Todos los derechos reservados. - v1.5.0</p>
</footer>

<!-- JS base -->
<script src="<?= asset('assets/js/jquery-3.6.0.min.js')?>"></script>

<script src="<?= asset('assets/js/jquery.dataTables-1.13.4.min.js')?>"></script>
<script src="<?= asset('assets/js/dataTables.bootstrap5.min.js')?>"></script>

<script src="<?= asset('assets/js/bootstrap.bundle.min.js')?>"></script>

<script src="<?= asset('assets/js/security.js') ?>"></script>

<!-- JS personalizados -->
<?php if (!empty($customScripts)) echo implode("\n", $customScripts); ?>

<script>
  function adjustDataTables(){
    if (window.jQuery && $.fn.dataTable) {
      $('.dataTable').each(function(){
        try { $(this).DataTable().columns.adjust().draw(false); } catch(e){}
      });
    }
  }

  function resetFotoINE() {
    const input = document.getElementById('foto_ine');
    const preview = document.getElementById('ineFotoPreview');

    if (input) {
        input.value = ''; // Limpia el archivo seleccionado
    }

    if (preview) {
        const defaultSrc = preview.getAttribute('data-default-src');
        preview.src = defaultSrc; // Regresa al placeholder
    }
  }

  // Colapsar/expandir sidebar en escritorio (UNIFICADO)
  document.getElementById('btnCollapseSidebar')?.addEventListener('click', function () {
    document.body.classList.toggle('with-sidebar-collapsed');
    document.querySelector('.sidebar')?.classList.toggle('collapsed');
    document.querySelector('.main-container')?.classList.toggle('collapsed');

    const sb = document.querySelector('.sidebar');
    if (sb){
      const onEnd = () => { adjustDataTables(); sb.removeEventListener('transitionend', onEnd); };
      sb.addEventListener('transitionend', onEnd);
      setTimeout(adjustDataTables, 250);
    } else {
      adjustDataTables();
    }
  });

  // Cuando se cierra el offcanvas en móvil, reajusta DataTables
  document.getElementById('offcanvasMenu')?.addEventListener('hidden.bs.offcanvas', adjustDataTables);

  // Hover-flyout SOLO: desktop + colapsado
  (function () {
    const MQ = 768;
    const isDesktop = () => window.innerWidth >= MQ;
    const isCollapsed = () => document.body.classList.contains('with-sidebar-collapsed');

    const setOpen = (dd, open) => {
      const toggle = dd.querySelector('[data-bs-toggle="dropdown"]');
      const menu = dd.querySelector('.dropdown-menu');
      if (!toggle || !menu) return;
      dd.classList.toggle('show', open);
      menu.classList.toggle('show', open);
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
      if (isDesktop() && isCollapsed()) enableHoverFlyout();
      else closeAll();
    };

    window.addEventListener('resize', onChange);
    document.addEventListener('DOMContentLoaded', () => { enableHoverFlyout(); onChange(); });
  })();

  // aria-label automático a links del sidebar
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.sidebar .nav-link').forEach(a => {
      if (!a.hasAttribute('aria-label')) {
        const span = a.querySelector('span');
        const text = span ? span.textContent.trim() : a.textContent.trim();
        if (text) a.setAttribute('aria-label', text);
      }
    });
  });

  window.addEventListener('load', function () {
    resetFotoINE();
  });
</script>

</body>
</html>
