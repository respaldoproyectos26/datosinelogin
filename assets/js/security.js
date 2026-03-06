// datosinelogin/assets/js/security.js
// Medidas cosméticas para producción (NO son seguridad real)

(function () {
    const hostname = location.hostname;
    const pathname = location.pathname;

    // 1) Entorno local: no bloquear nada (para que puedas depurar tranquilo)
    const isLocalHost = (hostname === 'localhost' || hostname === '127.0.0.1');

    // 2) Rutas donde SÍ queremos permitir depuración (login admin)
    //    Funciona tanto en local (/elec2025/naucalpan/...) como en producción (/naucalpan/...)
    const DEBUG_FRAGMENTS = [
        '/datosinelogin/',          // carpeta de logins en producción
        'elec2025/datosinelogin/'   // carpeta de logins en localhost (ruta típica)
    ];

    const isDebugRoute = DEBUG_FRAGMENTS.some(fragment => pathname.includes(fragment));

    // 3) Flag global opcional para emergencias:
    //    En alguna página puedes poner:
    //    <script>window.SEC_ALLOW_DEBUG = true;</script>
    const globalDebugFlag =
        (typeof window.SEC_ALLOW_DEBUG !== 'undefined') ? !!window.SEC_ALLOW_DEBUG : false;

    // ➜ Si estamos en local, o en ruta de login, o con flag global: NO aplicar bloqueos
    if (isLocalHost || isDebugRoute || globalDebugFlag) {
        return;
    }

    // ==============================
    //  A partir de aquí: PRODUCCIÓN
    //  y NO es login / debug
    // ==============================

    // 4) Opcional: "silenciar" la consola para que no vean tus logs/errores
    if (typeof console !== 'undefined') {
        const noop = function () {};
        ['log', 'info', 'warn', 'error', 'debug'].forEach(fn => {
            if (console[fn]) {
                console[fn] = noop;
            }
        });
    }

    // 5) Bloquear clic derecho (context menu)
    document.addEventListener('contextmenu', e => {
        e.preventDefault();
    });

    // 6) Bloquear atajos típicos de curiosos (Ctrl+U, F12, Ctrl+Shift+I, etc.)
    document.addEventListener('keydown', e => {
        const key = (e.key || '').toLowerCase();
        if (
            e.keyCode === 123 ||                             // F12
            (e.ctrlKey && key === 'u') ||                    // Ver código fuente
            (e.ctrlKey && key === 's') ||                    // Guardar página
            (e.ctrlKey && key === 'c') ||                    // Copiar global
            (e.ctrlKey && e.shiftKey && key === 'i') ||      // DevTools
            (e.ctrlKey && e.shiftKey && key === 'j')         // Consola
        ) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // 7) Hacer no seleccionable todo lo que tenga clase .bloqueo-copia
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.bloqueo-copia').forEach(el => {
            el.style.userSelect = 'none';
        });
    });

    // 8) Detección MUY básica de DevTools por diferencia de tamaños
    const threshold = 160;

    const showUnauthorized = () => {
        document.body.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:center;height:100vh;">
                <h1 style="font-family:sans-serif;text-align:center;">
                    Acceso no autorizado
                </h1>
            </div>
        `;
    };

    const checkDevTools = () => {
        const widthDiff  = window.outerWidth  - window.innerWidth;
        const heightDiff = window.outerHeight - window.innerHeight;

        if (widthDiff > threshold || heightDiff > threshold) {
            showUnauthorized();
            clearInterval(devCheck);
            window.removeEventListener('resize', checkDevTools);
        }
    };

    const devCheck = setInterval(checkDevTools, 1000);
    window.addEventListener('resize', checkDevTools);
})();
