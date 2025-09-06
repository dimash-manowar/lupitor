<?php
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
  'lifetime' => 0,
  'path'     => '/',   // ← clave: raíz, no /ajedrez ni /Ajedrez
  'domain'   => '',
  'secure'   => false, // true si usas https
  'httponly' => true,
  'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
// Auto-login por cookie remember (si no hay usuario en sesión)
if (empty($_SESSION['user']) && !empty($_COOKIE['remember'])) {
    [$selector, $validator] = explode(':', $_COOKIE['remember']) + [null, null];
    if ($selector && $validator) {
        $tokenModel = new AuthTokenModel();
        $tok = $tokenModel->findBySelector($selector);
        if ($tok && hash_equals($tok['validator_hash'], hash('sha256', $validator)) && strtotime($tok['expires_at']) > time()) {
            // Cargar usuario y crear sesión
            $usuarioModel = new UsuarioModel();
            $user = $usuarioModel->buscarPorId((int)$tok['user_id']);
            if ($user) {
                $_SESSION['user'] = [
                    'id'     => $user['id'],
                    'nombre' => $user['nombre'],
                    'email'  => $user['email'],
                    'rol'    => $user['rol']
                ];
                session_regenerate_id(true);
            }
        } else {
            // token inválido/expirado -> limpiar
            if (!empty($tok['id'])) $tokenModel->deleteById((int)$tok['id']);
            setcookie('remember', '', time() - 3600, '/');
        }
    }
}

// Core del proyecto
require_once __DIR__ . "/Config/Config.php";
require_once __DIR__ . "/Core/Controlador.php";
require_once __DIR__ . "/Core/BaseDatos.php";
require_once __DIR__ . "/Core/Mysql.php";
require_once BASE_PATH . '/Core/Pdf.php';
require_once __DIR__ . "/Helpers/Helpers.php";
// Composer autoload (PHPMailer)
$vendor = BASE_PATH . 'vendor/autoload.php';
if (is_file($vendor)) require_once $vendor;

// Cargar helpers de correo (define enviarCorreo(), buildResetEmailTemplate(), etc.)
require_once BASE_PATH . 'Helpers/Mailer.php';

// Autoload simple de Controladores
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . "/Controladores/{$class}.php",
        __DIR__ . "/Models/{$class}.php",
    ];
    foreach ($paths as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

// Capturar URL limpia
$path  = $_GET['url'] ?? '';
$path  = trim(filter_var($path, FILTER_SANITIZE_URL), '/');
$parts = $path ? explode('/', $path) : [];

// Resolver controlador y método
$controllerName = !empty($parts[0]) ? ucfirst($parts[0]) : 'Home';
$methodName     = $parts[1] ?? 'index';
$args           = array_slice($parts, 2);

// Cargar controlador
$controllerFile = __DIR__ . "/Controladores/{$controllerName}.php";
if (!is_file($controllerFile)) {
    exit("Controlador '{$controllerName}' no encontrado.");
}
require_once $controllerFile;

$controller = new $controllerName();
if (!method_exists($controller, $methodName)) {
    exit("Método '{$methodName}' no encontrado en '{$controllerName}'.");
}

// Convertir los argumentos de la URL
$args = array_map(function($arg) {
    // Si es numérico → int
    if (is_numeric($arg)) {
        return (int) $arg;
    }
    // En caso contrario → string limpio
    return trim((string)$arg);
}, $args);

// Llamada al método con los argumentos convertidos
call_user_func_array([$controller, $methodName], $args);
