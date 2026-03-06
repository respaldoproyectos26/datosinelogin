<?php
# Helper de autenticación (auth.php)
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/permissions.php';
#Quitar los requires si en todos lados ya se cambió a bootstrap.php

function login($usuario, $password) {
  $stmt = db()->prepare("
    SELECT u.*, r.name AS rol
    FROM ine_usuarios_sanluis u
    LEFT JOIN role_user ru ON ru.user_id = u.id
    LEFT JOIN roles r ON r.id = ru.role_id
    WHERE u.usuario = :u
  ");
  $stmt->execute([':u' => $usuario]);
  $user = $stmt->fetch();

  // Validar credenciales
  if (!$user || !password_verify($password, $user['pass_hash'])) {
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    return false;
  }

  // Login exitoso
  $_SESSION['login_attempts'] = 0;
  session_regenerate_id(true);

  $_SESSION['user'] = [
    'id'      => $user['id'],
    'usuario' => $user['usuario'],
    'rol'     => $user['rol'] ?? 'sin_rol',
    'estado'     => $user['estado'] ?? NULL,
  ];

  load_roles_permissions_to_session($user['id']); // Si ya usas permisos

  return true;
}

function user() {
  return $_SESSION['user'] ?? null;
}

function is_logged_in() {
  return isset($_SESSION['user']);
}

function austero_base_url(): string {
  // BASE_URL del FULL: /datosinelogin  ó  /elec2025/datosinelogin
  return preg_replace('~/datosinelogin$~', '/datosineloginaustero', BASE_URL);
}

function require_login(): void {
  if (!is_logged_in()) {
    require_once __DIR__ . '/flash.php';
    flash('error', 'Debes iniciar sesión');
    header('Location: ' . rtrim(BASE_URL,'/') . '/login.php');
    exit;
  }

  // SOLO si está habilitado el proyecto austero
  // USAR_PROYECTO_AUSTERO = false → todos entran normal
  // USAR_PROYECTO_AUSTERO = true → solo admin entra al FULL
  if (defined('USAR_PROYECTO_AUSTERO') && USAR_PROYECTO_AUSTERO === true) {

    $rol = $_SESSION['user']['rol'] ?? '';
    if ($rol !== 'admin') {

      $_SESSION = [];
      if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
          $params["path"], $params["domain"],
          $params["secure"], $params["httponly"]
        );
      }
      session_destroy();

      header('Location: ' . austero_base_url() . '/login.php');
      exit;
    }
  }
}

function logout() {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
  header('Location: ' . BASE_URL . '/login.php');
  exit;
}

function current_user_id() {
  return $_SESSION['user']['id'] ?? null;
}

function require_role($rol) {
  if (!is_logged_in() || ($_SESSION['user']['rol'] ?? '') !== $rol) {
    http_response_code(403);
    if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
      echo json_encode(['ok'=>false,'msg'=>'Acceso denegado']); exit;
    }
    exit('Acceso denegado');
  }
}

function tiene_permiso($permiso) {
  $perms = $_SESSION['permissions'] ?? [];
  return in_array($permiso, $perms, true);
}

function auth() {
  return new class {
    public function user() { return $_SESSION['user'] ?? null; }

    public function roleIds(): array {
      static $cache = [];  // cache por usuario (por request)
      $u = $this->user();
      if (!$u) return [];

      $uid = (int)($u['id'] ?? 0);
      if ($uid <= 0) return [];

      if (array_key_exists($uid, $cache)) {
        return $cache[$uid];
      }

      $st = db()->prepare("SELECT role_id FROM role_user WHERE user_id = ?");
      $st->execute([$uid]);

      // opcional: castear a int para consistencia
      $cache[$uid] = array_map('intval', $st->fetchAll(PDO::FETCH_COLUMN));
      return $cache[$uid];
    }

    public function can(string $perm): bool {
      return in_array($perm, $_SESSION['permissions'] ?? [], true);
    }
  };
}

function denegar_acceso()
{
    require_once __DIR__ . '/url.php';

    if (!headers_sent()) {
        header('Location: ' . HELPERS_URL . '/denegado.php');
        exit;
    }

    // Fallback si ya hubo salida HTML — evita el warning
    echo "<script>
        alert('Acceso denegado');
        window.location.href='" . app_url('helpers/denegado.php') . "';
    </script>";
    exit;
}

function exams_pending_count(): int
{
    if (!is_logged_in()) {
        return 0;
    }

    $pdo = db();
    $userId = current_user_id();
    
    // Obtener roles del usuario
    $roles = $pdo->prepare("SELECT role_id FROM role_user WHERE user_id = ?");
    $roles->execute([$userId]);
    $roleIds = array_column($roles->fetchAll(PDO::FETCH_ASSOC), 'role_id');

    // Query base
    $sql = "
        SELECT COUNT(DISTINCT e.id)
        FROM exams e
        JOIN exam_assignments a ON a.exam_id = e.id
        WHERE e.status = 'published'
          AND (
                a.user_id = ?
                OR (a.role_id IS NOT NULL " . 
                    ($roleIds ? " AND a.role_id IN (" . implode(',', array_fill(0, count($roleIds), '?')) . ")" : "") . 
                ")
              )
          AND e.id NOT IN (
                SELECT exam_id FROM attempts 
                WHERE user_id = ? AND submitted_at IS NOT NULL
          )
    ";

    // Parámetros
    $params = [$userId];
    if ($roleIds) {
        $params = array_merge($params, $roleIds);
    }
    $params[] = $userId;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}