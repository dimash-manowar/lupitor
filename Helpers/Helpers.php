<?php

// ----------------------
// CSRF Protection
// ----------------------
function csrfToken(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time']  = time();
    }
    return $_SESSION['csrf_token'];
}

function verificarCsrf(string $token, int $ttl = 7200): bool {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $ok = hash_equals($_SESSION['csrf_token'] ?? '', $token);
    if (!$ok) return false;
    if (!empty($_SESSION['csrf_time']) && (time() - $_SESSION['csrf_time']) > $ttl) return false;
    return true;
}

// ----------------------
// Sanitización y validaciones
// ----------------------
function limpiar($s) {
    return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}

function is_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ----------------------
// Utilidades de texto
// ----------------------
function fechaCastellano($fecha) {
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));

    $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");

    $nombreDia = str_replace($dias_EN, $dias_ES, $dia);
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);

    return "$nombreDia $numeroDia de $nombreMes de $anio";
}

function resumen($texto, $max = 120) {
    $texto = strip_tags($texto);
    if (strlen($texto) <= $max) return $texto;
    return substr($texto, 0, strrpos(substr($texto, 0, $max), ' ')) . '...';
}

if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = preg_replace('~[^-a-z0-9]+~', '', $text);
        return $text ?: bin2hex(random_bytes(4));
    }
}

// ----------------------
// Redirección
// ----------------------
function redir($url) {
    header("Location: $url");
    exit;
}

// ----------------------
// SQL Helpers
// ----------------------
function placeholdersIN(string $column, array $values): array {
    if (empty($values)) return ['1=0', []];
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    return ["$column IN ($placeholders)", $values];
}

// ----------------------
// Navegación e intención
// ----------------------
function currentUrl(): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? null) == 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri    = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}

function setIntended(): void {
    $url  = currentUrl();
    $path = parse_url($url, PHP_URL_PATH) ?? '/';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return;
    if (preg_match('~^/(auth|Assets|vendor)/~i', $path)) return;
    $_SESSION['intended'] = $url;
}
function popIntended(): ?string {
    $u = $_SESSION['intended'] ?? null;
    unset($_SESSION['intended']);
    return $u;
}

// ----------------------
// Gestión de imágenes
// ----------------------
function uploadImage(array $file, string $subdir = 'noticias', array $allowedExt = ['jpg','jpeg','png','webp'], int $maxMB = 5): array {
    if (!isset($file['error']) || ($file['error'] === UPLOAD_ERR_NO_FILE)) {
        return [false, 'No se ha seleccionado archivo'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [false, 'Error de subida (código ' . (int)$file['error'] . ')'];
    }
    $tmp  = $file['tmp_name'] ?? '';
    $name = $file['name']     ?? '';
    $size = (int)($file['size'] ?? 0);
    if (!$tmp || !is_uploaded_file($tmp)) {
        return [false, 'Subida inválida'];
    }
    $maxBytes = $maxMB * 1024 * 1024;
    if ($size <= 0 || $size > $maxBytes) {
        return [false, 'El archivo supera ' . $maxMB . ' MB'];
    }
    $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return [false, 'Formato no permitido: ' . $ext];
    }
    $info = @getimagesize($tmp);
    if ($info === false) {
        return [false, 'El archivo no parece ser una imagen válida'];
    }
    $safeBase = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($name, PATHINFO_FILENAME));
    $final    = $safeBase . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!defined('BASE_PATH')) {
        define('BASE_PATH', __DIR__ . '/../');
    }
    $dir = rtrim(BASE_PATH, '/\\') . '/Assets/img/' . trim($subdir, '/\\') . '/';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $dest = $dir . $final;
    if (!@move_uploaded_file($tmp, $dest)) {
        return [false, 'No se pudo mover el archivo al destino'];
    }
    return [true, $final];
}

function deleteFile(string $filename, string $subdir = 'noticias'): bool {
    if ($filename === '') return false;
    $filename = basename($filename);
    $dir  = rtrim(BASE_PATH, '/\\') . '/Assets/img/' . trim($subdir, '/\\') . '/';
    $path = $dir . $filename;
    return is_file($path) ? @unlink($path) : false;
}



// ----------------------
// homeCardsHtml - Tarjetas principales del home
// ----------------------
function homeCardsHtml(): string {
    return <<<HTML
<div class="row g-4 py-4">
  <!-- Tarjeta: Sobre el Club -->
  <div class="col-md-4">
    <div class="card h-100 text-white bg-primary border-0 shadow rounded-4">
      <div class="card-body text-center">
        <i class="bi bi-people display-4 mb-3 text-white"></i>
        <h5 class="card-title">Sobre el Club</h5>
        <p class="card-text">Descubre quiénes somos, nuestra historia y por qué amamos el ajedrez.</p>
        <a href="/club" class="btn btn-outline-light mt-2">Conócenos</a>
      </div>
    </div>
  </div>

  <!-- Tarjeta: Noticias -->
  <div class="col-md-4">
    <div class="card h-100 text-white bg-secondary border-0 shadow rounded-4">
      <div class="card-body text-center">
        <i class="bi bi-newspaper display-4 mb-3"></i>
        <h5 class="card-title">Noticias</h5>
        <p class="card-text">Actualidad del club, resultados y mucho más.</p>
        <a href="/noticias" class="btn btn-light mt-2">Ver noticias</a>
      </div>
    </div>
  </div>

  <!-- Tarjeta: Eventos -->
  <div class="col-md-4">
    <div class="card h-100 text-white bg-success border-0 shadow rounded-4">
      <div class="card-body text-center">
        <i class="bi bi-calendar-event display-4 mb-3"></i>
        <h5 class="card-title">Eventos</h5>
        <p class="card-text">Simultáneas, torneos sociales y clases especiales.</p>
        <a href="/eventos" class="btn btn-outline-light mt-2">Ver eventos</a>
      </div>
    </div>
  </div>
</div>
HTML;
}

// Valida #RRGGBB o #RGB (devuelve string normalizado #RRGGBB o null)
function validarColorHex(?string $hex): ?string {
    $hex = trim((string)$hex);
    if ($hex === '') return null;
    if ($hex[0] !== '#') $hex = '#'.$hex;
    if (!preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', $hex)) return null;
    // Normaliza #RGB -> #RRGGBB
    if (strlen($hex) === 4) {
        $hex = sprintf('#%1$s%1$s%2$s%2$s%3$s%3$s', $hex[1], $hex[2], $hex[3]);
    }
    return strtolower($hex);
}

function isLoggedIn(): bool {
  return !empty($_SESSION['user']);
}
// Helpers/Helpers.php
function isAjax(): bool {
  return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
      || str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
}

function requireLogin(): void {
  if (!isLoggedIn()) {
    if (isAjax()) {
      http_response_code(401);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['ok'=>false,'error'=>'login_required','redirect'=>BASE_URL.'auth/login']);
      exit;
    }
    $_SESSION['flash_error'] = 'Debes iniciar sesión';
    $next = urlencode($_SERVER['REQUEST_URI'] ?? '/');
    header('Location: ' . BASE_URL . 'auth/login?next=' . $next);
    exit;
  }
}

function requireAdmin(): void {
  requireLogin();
  if (strtolower((string)($_SESSION['user']['rol'] ?? '')) !== 'admin') {
    if (isAjax()) {
      http_response_code(403);
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['ok'=>false,'error'=>'forbidden']);
      exit;
    }
    $_SESSION['flash_error'] = 'Acceso denegado';
    header('Location: ' . BASE_URL);
    exit;
  }
}

