<?php
class AdminAjustes extends Controlador
{
  private SettingsModel $settings;

  public function __construct(){
    parent::__construct(); requireAdmin();
    $this->settings = new SettingsModel();
   
  }

  // GET /AdminAjustes/mensajes
  public function mensajes(): void {
    $maxMb = $this->settings->getInt('msg_max_mb', 15);
    $data = [
      'titulo' => 'Ajustes · Mensajes',
      'csrf'   => csrfToken(),
      'maxMb'  => $maxMb
    ];
    $this->view('Admin/ajustes-mensajes', $data);
  }

  // POST /AdminAjustes/mensajesGuardar
  public function mensajesGuardar(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
      redir(BASE_URL.'AdminAjustes/mensajes');
    }
    $mb = (int)($_POST['msg_max_mb'] ?? 15);
    $mb = max(1, min(100, $mb)); // de 1 a 100 MB
    $this->settings->set('msg_max_mb', (string)$mb);
    $_SESSION['flash_ok'] = 'Ajuste guardado (' . $mb . ' MB).';
    redir(BASE_URL.'AdminAjustes/mensajes');
  }
  public function notificaciones()
    {
        $uid   = (int)($_SESSION['user']['id'] ?? 0);
        $prefs = $this->model->obtenerPreferenciasNotificacion($uid) ?? ['notif_email_mensajes'=>1,'notif_email_alertas'=>1];

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

        $this->model->actualizarPreferenciasNotificacion($uid, $mensajes, $alertas);

        // Refresca la sesión si quieres tenerlo a mano
        if (!empty($_SESSION['user'])) {
            $_SESSION['user']['notif_email_mensajes'] = $mensajes;
            $_SESSION['user']['notif_email_alertas']  = $alertas;
        }

        $_SESSION['flash_ok'] = 'Preferencias guardadas';
        redir(BASE_URL.'UsuarioAjustes/notificaciones');
    }
}
