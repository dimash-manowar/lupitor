<?php
class Contacto extends Controlador
{
    private MensajeModel $mensajeModel;
    public function __construct()
    {
        parent::__construct();
        $this->mensajeModel = new MensajeModel();
    }

    public function index()
    {
        $data = ["titulo" => "Contacto", 'csrf' => csrfToken()];
        $this->view("Contacto/index", $data);
    }


    public function enviar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'contacto');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $asunto = trim($_POST['asunto'] ?? '');
        $cuerpo = trim($_POST['mensaje'] ?? '');
        if (!$nombre || !$email || !$asunto || !$cuerpo) {
            $_SESSION['flash_error'] = 'Completa todos los campos';
            header('Location: ' . BASE_URL . 'contacto');
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Email inválido';
            header('Location: ' . BASE_URL . 'contacto');
            exit;
        }
        $this->mensajeModel->crear($nombre, $email, $asunto, $cuerpo);
        $notif = new NotificacionModel();
        $notif->crearParaAdmins(
            'Nuevo mensaje de contacto',
            $nombre . ' — ' . $asunto,
            'info',
            BASE_URL . 'admin/mensajes'
        );
        $_SESSION['flash_ok'] = 'Mensaje enviado, te responderemos pronto.';
        header('Location: ' . BASE_URL . 'contacto');
        exit;
    }
}
