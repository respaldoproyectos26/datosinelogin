<?php
require_once __DIR__ . '/../config/config.php';

require_once HELPERS_DIR . '/errores.php';
require_once HELPERS_DIR . '/cache_headers.php';

require_once HELPERS_DIR . '/prefix.php';
require_once HELPERS_DIR . '/db.php';

require_once HELPERS_DIR . '/permissions.php';   // carga roles/permisos a sesión
require_once HELPERS_DIR . '/auth.php';          // usa db + permissions

require_once HELPERS_DIR . '/url.php';           // asset/app_url/nav_*
require_once HELPERS_DIR . '/response.php';
require_once HELPERS_DIR . '/flash.php';
require_once HELPERS_DIR . '/csrf.php';

