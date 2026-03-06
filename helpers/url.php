<?php
/*
📘 Documentación — Helpers de URLs (url.php)

Archivo: /includes/url.php o /includes/helpers.php
Requiere: config.php (para BASE_URL y APP_ROOT)

🔹 1. path_join(...$segments): string
📄 Descripción

Une partes de una ruta de forma segura, eliminando barras duplicadas (//), resolviendo . y ...

✅ Ejemplo
echo path_join('/var/www', 'datosinelogin', '../assets/css/');
// Resultado: /var/www/assets/css

💡 Uso común

Internamente usado para componer rutas de archivos del servidor (no URLs de navegador).

🔹 2. base_url(string $path = ''): string
📄 Descripción

Devuelve la URL completa (absoluta) de la aplicación según BASE_URL y el dominio actual.
Funciona igual en local o en producción.

✅ Ejemplo
echo base_url('bienvenida.php');


En local:
http://localhost/elec2025/datosinelogin/bienvenida.php

En servidor:
https://midominio.com/datosinelogin/bienvenida.php

💡 Uso común

Para generar URLs completas (por ejemplo, redirecciones o enlaces externos).

🔹 3. app_url(string $path = ''): string
📄 Descripción

Alias de base_url(), pensada para uso interno en vistas y menús.
Devuelve la URL relativa al proyecto, respetando BASE_URL.

✅ Ejemplo
<a href="<?= app_url('dashboard.php') ?>">Ir al panel</a>


En local: /elec2025/datosinelogin/dashboard.php
En servidor: /datosinelogin/dashboard.php

💡 Uso común

En templates o menús HTML cuando solo necesitas la ruta interna (sin dominio completo).

🔹 4. asset(string $relPath): string
📄 Descripción

Devuelve la URL de un recurso estático (CSS, JS, imagen, etc.)
y le añade un parámetro de versión ?v=timestamp basado en la fecha de modificación del archivo.
Esto fuerza al navegador a recargar los cambios cuando actualizas un asset.

✅ Ejemplo
<link rel="stylesheet" href="<?= asset('assets/css/estilos.css') ?>">


Resultado:
/elec2025/datosinelogin/assets/css/estilos.css?v=1730561200

(Si cambias estilos.css, el timestamp cambia automáticamente.)

💡 Uso común

Para evitar problemas de caché con CSS o JS.

🔹 5. nav_active(string $needle): string
📄 Descripción

Devuelve la clase CSS "active" si la ruta actual coincide o empieza con el fragmento dado ($needle).
Muy útil para marcar qué opción de menú está activa.

✅ Ejemplo
<li class="nav-item <?= nav_active('inicio.php') ?>">
  <a href="<?= app_url('inicio.php') ?>">Inicio</a>
</li>


Si estás en /datosinelogin/inicio.php → devuelve:
active

Si estás en otra página → devuelve:
'' (vacío)

💡 Uso común

En menús de navegación, tabs o breadcrumbs.

🔹 6. module_url(string $module, string $path = ''): string
📄 Descripción

Construye URLs para módulos o subcarpetas dentro de tu app.
Te evita concatenar manualmente los nombres de módulos.

✅ Ejemplo
echo module_url('usuarios', 'editar.php?id=5');


En local: /elec2025/datosinelogin/usuarios/editar.php?id=5
En servidor: /datosinelogin/usuarios/editar.php?id=5

💡 Uso común

Cuando trabajas con módulos o subdirectorios dentro del proyecto (por ejemplo, /usuarios/, /reportes/, etc.).

🔹 7. (Opcional) asset_url(string $path = ''): string

En algunos setups está incluida como alias.

📄 Descripción

Devuelve una URL relativa al directorio /assets/.

✅ Ejemplo
<script src="<?= asset_url('js/app.js') ?>"></script>


→ /elec2025/datosinelogin/assets/js/app.js

💡 Uso común

Alternativa rápida a asset(), sin control de caché.

🧾 Resumen rápido (chuleta)
Función	Devuelve	Uso principal
path_join('a', '../b')	/b	Manipular rutas de archivos (servidor)
base_url('archivo.php')	URL completa (con dominio)	Redirecciones, APIs, enlaces externos
app_url('archivo.php')	Ruta relativa (desde BASE_URL)	Enlaces internos en HTML
asset('assets/css/app.css')	Ruta + versión (?v=)	CSS, JS, imágenes (evitar caché)
nav_active('inicio')	"active" o ""	Menús y tabs activos
module_url('modulo', 'vista.php')	/modulo/vista.php	Módulos o subcarpetas
asset_url('js/app.js')	/assets/js/app.js	Variante rápida sin versión
🧠 Tips finales

En vistas HTML usa app_url() o asset(), nunca rutas absolutas manuales.

En controladores o redirecciones usa base_url().

Para marcar navegación activa, usa nav_active().

Si agregas nuevos módulos (ej. /reportes/), usa module_url('reportes', 'index.php').
*/

###############################################################################
####################### COMPATIBILIDAD Y MEJORAS ##############################
###############################################################################

require_once __DIR__ . '/../config/config.php';

/**
 * --------------------------------------------------
 * Helpers de rutas (filesystem / URL)
 * --------------------------------------------------
 */

/**
 * path_join — une rutas de forma segura (estilo filesystem)
 */
if (!function_exists('path_join')) {
    function path_join(string ...$segments): string {
        $path = implode('/', $segments);
        $path = preg_replace('~/{2,}~', '/', $path);

        $parts = [];
        foreach (explode('/', $path) as $seg) {
            if ($seg === '' || $seg === '.') continue;
            if ($seg === '..') {
                array_pop($parts);
            } else {
                $parts[] = $seg;
            }
        }

        $joined = implode('/', $parts);
        if (str_starts_with($path, '/')) {
            $joined = '/' . $joined;
        }

        return $joined;
    }
}

/**
 * url_join — une base + path asegurando una sola /
 * (pensado para URLs, no filesystem)
 */
if (!function_exists('url_join')) {
    function url_join(string $base, string $path = ''): string {
        $base = rtrim($base, '/');
        $path = ltrim($path, '/');
        return $path === '' ? $base . '/' : $base . '/' . $path;
    }
}

/**
 * --------------------------------------------------
 * URLs base de la aplicación
 * --------------------------------------------------
 */

/**
 * base_url — genera URL absoluta (con scheme + host)
 */
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $https  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $https ? 'https://' : 'http://';
        $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';

        $base = rtrim(BASE_URL, '/');
        $full = $scheme . $host . $base;

        return $path
            ? $full . '/' . ltrim($path, '/')
            : $full . '/';
    }
}

/**
 * app_url — URL relativa a BASE_URL (sin scheme/host)
 */
if (!function_exists('app_url')) {
    function app_url(string $path = ''): string {
        $base = rtrim(BASE_URL, '/');
        return $path
            ? $base . '/' . ltrim($path, '/')
            : $base . '/';
    }
}

/**
 * --------------------------------------------------
 * Assets
 * --------------------------------------------------
 */

/**
 * asset — genera URL de assets con versionado
 *
 * - Usa filemtime si el archivo existe
 * - NO usa time() si no existe (evita romper cache y oculta errores)
 * - Funciona correctamente en subcarpetas
 */
if (!function_exists('asset')) {
    function asset(string $relPath): string {
        $relPath = ltrim($relPath, '/');

        $fsPath = defined('APP_ROOT')
            ? rtrim(APP_ROOT, '/') . '/' . $relPath
            : null;

        $ver = ($fsPath && is_file($fsPath))
            ? filemtime($fsPath)
            : null;

        $url = rtrim(BASE_URL, '/') . '/' . $relPath;

        return $ver ? ($url . '?v=' . $ver) : $url;
    }
}

/**
 * --------------------------------------------------
 * Navegación activa
 * --------------------------------------------------
 */

/**
 * nav_active — marca un link como activo
 */
if (!function_exists('nav_active')) {
    function nav_active(string $needle): string {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = rtrim(BASE_URL, '/');

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri    = trim($uri, '/');
        $needle = trim($needle, '/');

        return ($uri === $needle || str_starts_with($uri, $needle . '/'))
            ? 'active'
            : '';
    }
}

/**
 * nav_parent — marca un menú padre como activo
 */
if (!function_exists('nav_parent')) {
    function nav_parent(array $parents): string {
        $uri  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = rtrim(BASE_URL, '/');

        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = trim($uri, '/');

        foreach ($parents as $p) {
            $p = trim($p, '/');
            if ($uri === $p || str_starts_with($uri, $p . '/')) {
                return 'active menu-parent-active';
            }
        }

        return '';
    }
}

/**
 * --------------------------------------------------
 * Módulos
 * --------------------------------------------------
 */

/**
 * module_url — URLs internas de módulos
 */
if (!function_exists('module_url')) {
    function module_url(string $module, string $path = ''): string {
        return base_url(trim($module, '/') . '/' . ltrim($path, '/'));
    }
}