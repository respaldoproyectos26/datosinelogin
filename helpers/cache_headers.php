<?php
/**
 * set_cache_headers
 * Sistema optimizado de control de cache para HTML, APIs y archivos estáticos.
 * Incluye protección para endpoints autenticados (Vary: Cookie)
 * y modos avanzados para APIs.
 */

function set_cache_headers(string $kind = 'html') {
    // 🔒 Seguridad mínima: nunca cachear errores 500+
    if (http_response_code() >= 500) {
        header('Cache-Control: no-store');
        return;
    }

    switch ($kind) {

        /* -----------------------------------------------------------
         * ARCHIVOS ESTÁTICOS (CSS, JS, imágenes, íconos)
         * -----------------------------------------------------------*/
        case 'static':
        case 'icon':
            // Archivos versionados, cache por 1 año, inmutables
            header('Cache-Control: public, max-age=31536000, immutable');
            break;

        case 'manifest':
            // Manifest PWA: siempre verifica cambios
            header('Content-Type: application/manifest+json');
            header('Cache-Control: no-cache');
            break;

        /* -----------------------------------------------------------
         * HTML normal (páginas renderizadas)
         * -----------------------------------------------------------*/
        case 'html':
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: public, max-age=300, stale-while-revalidate=600');
            header('Vary: Cookie, Accept-Language'); // Evita mezclar sesiones
            break;        

        case 'html-nocache':
            // HTML con sesión/login: no cachear
            header('Content-Type: text/html; charset=utf-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Vary: Cookie');
            break;

        /* -----------------------------------------------------------
         * API BASE (datos que cambian frecuentemente)
         * Autenticada → private
         * -----------------------------------------------------------*/
        case 'api':
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: private, max-age=60, stale-while-revalidate=120');
            header('Vary: Accept, Cookie');  
            break;
        
        /* -----------------------------------------------------------
         * API AVANZADOS
         * -----------------------------------------------------------*/

        case 'api-fast':
            // ⚡ Datos que cambian poco, rápido para usuarios frecuentes
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: private, max-age=300, stale-while-revalidate=900');
            header('Vary: Accept, Cookie');
            break;

        case 'api-slow':
            // 🐢 Datos muy estables, ideal para CDN (Cloudflare)
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: public, max-age=3600, stale-while-revalidate=10800, immutable');
            header('Vary: Accept');
            break;

        case 'api-sensitive':
            // 🔐 Datos ultrasesibles: nunca cachear
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Vary: Cookie');
            break;

        /* -----------------------------------------------------------
         * DEFAULT
         * -----------------------------------------------------------*/
        default:
            header('Content-Type: text/plain; charset=utf-8');
            header('Cache-Control: no-store');
            break;
    }
}

/*
🧩 Qué hace cada modo y cuándo usarlo
✔️ static / icon
Archivos inmutables: .css, .js, .png, .jpg, .svg, etc.
Cache 1 año, requiere versionado por nombre (ej: app.v3.js).

✔️ manifest
PWA manifest: siempre verifica cambios.

✔️ html
Páginas públicas sin sesión: cache corto, Vary para cookies y lenguaje.

✔️ html-nocache
HTML con sesión/login: no cachea nada.

✔️ api
Datos frecuentes por usuario: privado, evita mezclar usuarios.

✔️ api-fast
Datos que cambian poco: rápido para usuarios frecuentes.

✔️ api-slow
Datos casi estáticos, público, ideal para CDN.

✔️ api-sensitive
Datos críticos o personales: nunca cachear.

🚀 Beneficios
🔒 Seguridad: evita mezclar usuarios y cookies.
⚡ Rendimiento: APIs rápidas, soporte CDN, stale-while-revalidate.
🧠 Inteligencia: no cachea errores 500, soporta HTML, APIs y estáticos.
*/
