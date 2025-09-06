<?php
class UsuarioAjustes extends Controlador
{
    private UsuarioModel $usuarioModel;

    public function __construct()
    {
        parent::__construct();
        requireLogin();
        $this->usuarioModel = new UsuarioModel();
    }

    public function notificaciones()
    {
        $uid   = (int)($_SESSION['user']['id'] ?? 0);
        $prefs = $this->usuarioModel->obtenerPreferenciasNotificacion($uid) ?? ['notif_email_mensajes'=>1,'notif_email_alertas'=>1];

        $data = [
            'titulo' => 'Preferencias de notificaciones',
            'csrf'   => csrfToken(),
            'prefs'  => $prefs
        ];
        $this->view('Usuario/Ajustes/notificaciones', $data);
    }

    public function notificacionesPost()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') { redir(BASE_URL.'UsuarioAjustes/notificaciones'); }
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['flash_error'] = 'CSRF inválido';
            redir(BASE_URL.'UsuarioAjustes/notificaciones');
        }

        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $mensajes = isset($_POST['notif_email_mensajes']) ? 1 : 0;
        $alertas  = isset($_POST['notif_email_alertas'])  ? 1 : 0;

        $this->usuarioModel->actualizarPreferenciasNotificacion($uid, $mensajes, $alertas);

        // Refresca la sesión si quieres tenerlo a mano
        if (!empty($_SESSION['user'])) {
            $_SESSION['user']['notif_email_mensajes'] = $mensajes;
            $_SESSION['user']['notif_email_alertas']  = $alertas;
        }

        $_SESSION['flash_ok'] = 'Preferencias guardadas';
        redir(BASE_URL.'UsuarioAjustes/notificaciones');
    }
}
