<?php

declare(strict_types=1);

class Auth extends Controlador
{
    private UsuarioModel $usuarioModel;
    private ?AuthTokenModel $tokenModel = null; // para "recuérdame"

    // Rate-limit (anti fuerza bruta)
    private int $maxAttempts = 5;   // intentos fallidos
    private int $lockMinutes = 10;  // minutos de bloqueo

    public function __construct()
    {
        parent::__construct();
        // Modelos
        $this->usuarioModel = new UsuarioModel();
        if (class_exists('AuthTokenModel')) {
            $this->tokenModel = new AuthTokenModel();
        }
    }
    public function index(): void
    {
        // Autologin por cookie "remember" si no hay sesión
        if (empty($_SESSION['user']) && !empty($_COOKIE['remember']) && class_exists('AuthTokenModel') && class_exists('UsuarioModel')) {
            [$selector, $validator] = explode(':', $_COOKIE['remember']) + [null, null];
            if ($selector && $validator) {
                $atm = new AuthTokenModel();
                $tok = $atm->findBySelector($selector);
                if ($tok && strtotime($tok['expires_at']) > time()) {
                    $calc = hash('sha256', $validator);
                    if (hash_equals($tok['validator_hash'], $calc)) {
                        $um = new UsuarioModel();
                        $u = $um->buscarPorId((int)$tok['user_id']);
                        if ($u) {
                            $_SESSION['user'] = [
                                'id'     => (int)$u['id'],
                                'nombre' => $u['nombre'],
                                'email'  => $u['email'],
                                'rol'    => $u['rol']
                            ];
                            session_regenerate_id(true);
                        }
                    }
                }
            }
        }
    }
    /* ===========================
       LOGIN
       =========================== */
    // GET /auth/login
    public function login(): void
    {
        $data = [
            "titulo" => "Iniciar sesión",
            "csrf" => csrfToken(),
            "hideNavbar" => true,
            "hideFooter" => true
        ];
        $this->view("Auth/login", $data);
    }


    // POST /auth/loginPost
    public function loginPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');

        // Rate-limit por IP
        $ip  = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = "rl_login_{$ip}";
        $_SESSION[$key] = $_SESSION[$key] ?? ["count" => 0, "locked_until" => 0];

        if (time() < $_SESSION[$key]['locked_until']) {
            $_SESSION['flash_error'] = 'Demasiados intentos. Inténtalo más tarde.';
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = !empty($_POST['remember']);

        if ($email === '' || $password === '') {
            $_SESSION['flash_error'] = 'Completa todos los campos';
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // ⚠️ Asegúrate de que buscarPorEmail selecciona 'rol'
        $user = $this->usuarioModel->buscarPorEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $_SESSION[$key]['count']++;
            if ($_SESSION[$key]['count'] >= $this->maxAttempts) {
                $_SESSION[$key]['locked_until'] = time() + ($this->lockMinutes * 60);
                $_SESSION[$key]['count'] = 0;
                $_SESSION['flash_error'] = 'Demasiados intentos. Bloqueado temporalmente.';
            } else {
                $_SESSION['flash_error'] = 'Credenciales incorrectas';
            }
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }

        // Éxito → sesión
        $_SESSION[$key] = ["count" => 0, "locked_until" => 0];
        $_SESSION['user'] = [
            'id'     => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email'  => $user['email'],
            'rol'    => $user['rol']   // <-- debe existir
        ];
        session_regenerate_id(true);

        // Remember me (antes de redirigir)
        if ($remember && $this->tokenModel) {
            $this->tokenModel->purgeExpired();
            $this->tokenModel->deleteByUser((int)$user['id']); // un token por usuario

            $selector  = rtrim(strtr(base64_encode(random_bytes(9)), '+/', '-_'), '=');
            $validator = bin2hex(random_bytes(16));
            $hash      = hash('sha256', $validator);
            $ua        = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
            $ipNow     = substr($ip, 0, 45);
            $expiresAt = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

            $this->tokenModel->create((int)$user['id'], $selector, $hash, $ua, $ipNow, $expiresAt);

            $cookieVal = $selector . ':' . $validator;
            setcookie('remember', $cookieVal, [
                'expires'  => time() + 60 * 60 * 24 * 30,
                'path'     => '/',
                'domain'   => '',
                'secure'   => false, // true en HTTPS
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        // 1) ¿venía de ruta protegida?
        $redir = function_exists('popIntended') ? popIntended() : null;
        if (!empty($redir)) {
            header('Location: ' . $redir);
            exit;
        }

        // 2) ¿next explícito (POST o GET)?
        $next = $_POST['next'] ?? ($_GET['next'] ?? '');
        if (!empty($next)) {
            // evita loops a /auth/*
            $path = parse_url($next, PHP_URL_PATH) ?? '';
            if (!preg_match('~^/auth/~i', $path)) {
                header('Location: ' . $next);
                exit;
            }
        }

        // 3) si es admin → /admin
        $rol = strtolower(trim($_SESSION['user']['rol'] ?? ''));
        if ($rol === 'admin') {
            header('Location: ' . BASE_URL . 'admin');
            exit;
        }

        // 4) resto → panel de usuario
        header('Location: ' . BASE_URL . 'Usuario/index');
        exit;
    }



    /* ===========================
       REGISTRO
       =========================== */
    // GET /auth/registro
    public function registro(): void
    {
        $data = [
            "titulo" => "Crear cuenta",
            "csrf" => csrfToken(),
            "hideNavbar" => true,
            "hideFooter" => true
        ];
        $this->view("Auth/registro", $data);
    }

    // POST /auth/registroPost
    public function registroPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/registro');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');

        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $pass1  = $_POST['password']   ?? '';
        $pass2  = $_POST['password2']  ?? '';

        if (!$nombre || !$email || !$pass1 || !$pass2) {
            $_SESSION['flash_error'] = 'Completa todos los campos';
            header('Location: ' . BASE_URL . 'auth/registro');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email inválido';
            header('Location: ' . BASE_URL . 'auth/registro');
            exit;
        }
        if ($pass1 !== $pass2) {
            $_SESSION['flash_error'] = 'Las contraseñas no coinciden';
            header('Location: ' . BASE_URL . 'auth/registro');
            exit;
        }
        if (strlen($pass1) < 6) {
            $_SESSION['flash_error'] = 'La contraseña debe tener al menos 6 caracteres';
            header('Location: ' . BASE_URL . 'auth/registro');
            exit;
        }
        if ($this->usuarioModel->buscarPorEmail($email)) {
            $_SESSION['flash_error'] = 'Ya existe un usuario con ese email';
            header('Location: ' . BASE_URL . 'auth/registro');
            exit;
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = mb_strtolower(trim($_POST['email'] ?? ''), 'UTF-8');
        $pass1    = $_POST['password']  ?? '';
        $pass2    = $_POST['password2'] ?? '';
        $fotoRel  = null;

        // valida contraseñas
        if ($pass1 !== $pass2) { /* manejar error */
        }

        // subir foto (opcional)
        if (!empty($_FILES['foto']) && ($_FILES['foto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            [$ok, $res] = uploadImage($_FILES['foto'], 'perfiles', ['jpg', 'jpeg', 'png', 'webp'], 5);
            if ($ok) $fotoRel = 'Assets/img/perfiles/' . $res;
        }

        // crear usuario (modelo hashea internamente)
        $id = $this->usuarioModel->crear([
            'nombre'   => $nombre,
            'email'    => $email,
            'password' => $pass1,
            'rol'      => 'usuario',
            'foto_url' => $fotoRel,
        ]);
        if ($id) {
            // Email de bienvenida (no rompas el flujo si falla)
            $embedded = [];
            $cid = null;
            $logoPath = BASE_PATH . "Assets/img/logo.png";
            if (is_file($logoPath)) {
                $cid = "logo_" . bin2hex(random_bytes(4));
                $embedded[] = ['path' => $logoPath, 'cid' => $cid, 'name' => 'logo.png'];
            }            
            $loginLink = BASE_URL . 'auth/login';
            list($subject, $html, $text) = buildWelcomeEmailTemplate($loginLink, $nombre, $cid);
            enviarCorreo($email, $nombre, $subject, $html, $text, $embedded); // ignoramos retorno

            $_SESSION['flash_ok'] = 'Registro correcto. Ya puedes iniciar sesión.';
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }

    /* ===========================
       OLVIDÉ MI CONTRASEÑA
       =========================== */
    // GET /auth/forgot
    public function forgot(): void
    {
        $data = [
            "titulo" => "Recuperar contraseña",
            "csrf" => csrfToken(),
            "hideNavbar" => true,
            "hideFooter" => true
        ];
        $this->view("Auth/forgot", $data);
    }

    // POST /auth/forgotPost
    public function forgotPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');

        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $_SESSION['flash_error'] = 'Debes indicar un email';
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }

        $user = $this->usuarioModel->buscarPorEmail($email);
        if (!$user || empty($user['id'])) {
            $_SESSION['flash_error'] = 'No existe ninguna cuenta con ese email';
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }

        $resetModel = new PasswordResetModel();
        $resetModel->deleteByUser((int)$user['id']);
        $resetModel->purgeExpired();

        $selector  = rtrim(strtr(base64_encode(random_bytes(9)), '+/', '-_'), '=');
        $validator = bin2hex(random_bytes(16));
        $hash      = hash('sha256', $validator);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600);
        $resetModel->crear((int)$user['id'], $selector, $hash, $expiresAt);

        $link = BASE_URL . "auth/reset/$selector/$validator";

        $embedded = [];
        $cid = null;
        $logoPath = BASE_PATH . "Assets/img/logo.png";
        if (is_file($logoPath)) {
            $cid = "logo_ajedrez_" . bin2hex(random_bytes(4));
            $embedded[] = ['path' => $logoPath, 'cid' => $cid, 'name' => 'logo.png'];
        }
        if (!function_exists('buildResetEmailTemplate')) {
            require_once BASE_PATH . 'Helpers/Mailer.php';
        }
        list($subject, $html, $text) = buildResetEmailTemplate($link, $user['nombre'] ?? null, $cid);

        enviarCorreo($user['email'], $user['nombre'] ?? '', $subject, $html, $text, $embedded);

        $_SESSION['flash_ok'] = 'Si el email está registrado, recibirás un enlace de recuperación.';
        header('Location: ' . BASE_URL . 'auth/forgot');
        exit;
    }



    // GET /auth/reset/{selector}/{validator}
    public function reset(string $selector = '', string $validator = ''): void
    {
        $data = [
            "titulo" => "Restablecer contraseña",
            "csrf" => csrfToken(),
            "selector" => $selector,
            "validator" => $validator,
            "hideNavbar" => true,
            "hideFooter" => true
        ];
        $this->view("Auth/reset", $data);
    }

    // POST /auth/resetPost
    public function resetPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');

        $selector  = trim($_POST['selector']  ?? '');
        $validator = trim($_POST['validator'] ?? '');
        $pass1     = $_POST['password']  ?? '';
        $pass2     = $_POST['password2'] ?? '';

        if (!$selector || !$validator || !$pass1 || !$pass2) {
            $_SESSION['flash_error'] = 'Completa todos los campos';
            header('Location: ' . BASE_URL . "auth/reset/$selector/$validator");
            exit;
        }
        if ($pass1 !== $pass2) {
            $_SESSION['flash_error'] = 'Las contraseñas no coinciden';
            header('Location: ' . BASE_URL . "auth/reset/$selector/$validator");
            exit;
        }
        if (strlen($pass1) < 6) {
            $_SESSION['flash_error'] = 'La contraseña debe tener al menos 6 caracteres';
            header('Location: ' . BASE_URL . "auth/reset/$selector/$validator");
            exit;
        }

        $resetModel = new PasswordResetModel();
        $token = $resetModel->findBySelector($selector);

        if (
            !$token || strtotime($token['expires_at']) < time() ||
            !hash_equals($token['validator_hash'], hash('sha256', $validator))
        ) {
            $_SESSION['flash_error'] = 'Enlace inválido o caducado';
            header('Location: ' . BASE_URL . 'auth/forgot');
            exit;
        }

        // Actualizar contraseña del usuario
        $hash = password_hash($pass1, PASSWORD_DEFAULT);
        $this->usuarioModel->actualizarPassword((int)$token['user_id'], $hash);

        // Borrar token de reset
        $resetModel->deleteById((int)$token['id']);

        $_SESSION['flash_ok'] = 'Contraseña restablecida. Ya puedes iniciar sesión.';
        header('Location: ' . BASE_URL . 'auth/login');
        exit;
    }

    /* ===========================
       LOGOUT
       =========================== */
    public function logout(): void
    {
        // Borrar cookie remember y su token
        if (!empty($_COOKIE['remember'])) {
            [$selector] = explode(':', $_COOKIE['remember']) + [null, null];
            if ($selector && $this->tokenModel) {
                $tok = $this->tokenModel->findBySelector($selector);
                if ($tok) $this->tokenModel->deleteById((int)$tok['id']);
            }
            setcookie('remember', '', time() - 3600, '/');
        }

        // Limpiar sesión
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();

        header('Location: ' . BASE_URL);
        exit;
    }
}
