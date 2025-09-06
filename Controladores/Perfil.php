<?php
class Perfil extends Controlador
{
    private UsuarioModel $usuarioModel;


    public function __construct()
    {
        parent::__construct();
        $this->model = new UsuarioModel();
    }


    // GET /perfil
    public function index()
    {
        requireLogin();
        $user = currentUser();
        $data = [
            'titulo' => 'Mi perfil',
            'csrf' => csrfToken(),
            'user' => $user,
        ];
        $this->view('Perfil/index', $data);
    }


    // POST /perfil/actualizar
    public function actualizar()
    {
        requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');


        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');


        if (!$nombre || !$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Revisa los datos del formulario.';
            header('Location: ' . BASE_URL . 'perfil');
            exit;
        }


        $user = currentUser();


        // Validar email único si cambia
        if (strcasecmp($email, $user['email']) !== 0) {
            $existe = $this->usuarioModel->buscarPorEmail($email);
            if ($existe) {
                $_SESSION['flash_error'] = 'Ese email ya está registrado.';
                header('Location: ' . BASE_URL . 'perfil');
                exit;
            }
        }


        // Actualizar
        $filas = $this->model->actualizarDatos((int)$user['id'], $nombre, $email);


        // Refrescar sesión
        $_SESSION['user']['nombre'] = $nombre;
        $_SESSION['user']['email'] = $email;
    }
}
