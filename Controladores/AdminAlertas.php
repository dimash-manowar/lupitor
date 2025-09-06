<?php
class AdminAlertas extends Controlador
{
    private AlertaModel $alertaModel;
    private UsuarioModel $usuarioModel;
    private NotificacionModel $notifModel;

    public function __construct()
    {
        parent::__construct();
        requireAdmin();
        $this->alertaModel   = new AlertaModel();
        $this->usuarioModel  = new UsuarioModel();
        $this->notifModel    = new NotificacionModel();
    }

    public function crear()
    {
        $data = [
            'titulo' => 'Crear alerta',
            'csrf'   => csrfToken(),
        ];
        $this->view('Admin/Alertas/crear', $data);
    }

    public function crearPost()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') redir(BASE_URL.'AdminAlertas/crear');
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['flash_error'] = 'CSRF inválido';
            redir(BASE_URL.'AdminAlertas/crear');
        }

        $adminId  = (int)($_SESSION['user']['id'] ?? 0);
        $titulo   = trim((string)($_POST['titulo'] ?? ''));
        $cuerpo   = trim((string)($_POST['cuerpo'] ?? ''));
        $link     = trim((string)($_POST['link_url'] ?? '')) ?: null;
        $aud      = in_array($_POST['audiencia'] ?? 'todos', ['todos','usuarios','admins','segmento'], true) ? $_POST['audiencia'] : 'todos';
        $enviar   = isset($_POST['enviar_email']) ? 1 : 0;

        $segmento = ['ids'=>[], 'emails'=>[]];
        if ($aud === 'segmento') {
            $ids    = array_filter(array_map('intval', preg_split('/[\s,;]+/', (string)($_POST['ids'] ?? ''), -1, PREG_SPLIT_NO_EMPTY)));
            $emails = array_filter(array_map('trim', preg_split('/[\s,;]+/', (string)($_POST['emails'] ?? ''), -1, PREG_SPLIT_NO_EMPTY)));
            $segmento = ['ids'=>$ids, 'emails'=>$emails];
        }
        $segJson = json_encode($segmento, JSON_UNESCAPED_UNICODE);

        if ($titulo === '') {
            $_SESSION['flash_error'] = 'Falta el título';
            redir(BASE_URL.'AdminAlertas/crear');
        }

        $aid = $this->alertaModel->crear([
            'titulo'        => $titulo,
            'cuerpo'        => $cuerpo ?: null,
            'link_url'      => $link,
            'audiencia'     => $aud,
            'segmento_json' => $segJson,
            'enviar_email'  => $enviar,
            'creada_por'    => $adminId,
        ]);

        // ---- resolver destinatarios
        $destinatarios = [];
        if ($aud === 'todos') {
            $destinatarios = $this->usuarioModel->select("SELECT * FROM usuarios");
        } elseif ($aud === 'usuarios') {
            $destinatarios = $this->usuarioModel->select("SELECT * FROM usuarios WHERE LOWER(rol)='usuario'");
        } elseif ($aud === 'admins') {
            $destinatarios = $this->usuarioModel->select("SELECT * FROM usuarios WHERE LOWER(rol)='admin'");
        } else { // segmento
            $conds = []; $vals=[];
            if (!empty($segmento['ids']))    { $in = implode(',', array_fill(0,count($segmento['ids']), '?')); $conds[]="id IN ($in)";   $vals = array_merge($vals, $segmento['ids']); }
            if (!empty($segmento['emails'])) { $in = implode(',', array_fill(0,count($segmento['emails']), '?')); $conds[]="email IN ($in)"; $vals = array_merge($vals, $segmento['emails']); }
            if ($conds) {
                $sql = "SELECT * FROM usuarios WHERE ".implode(' OR ', $conds);
                $destinatarios = $this->usuarioModel->select($sql, $vals);
            } else {
                $destinatarios = [];
            }
        }

        // ---- crear notificación + email opcional
        $logoPath = rtrim(BASE_PATH, '/\\') . '/Assets/img/logo-ajedrez.png';
        $embedded = (is_file($logoPath) ? [['path'=>$logoPath,'cid'=>'logo-alert','name'=>'logo.png','type'=>'image/png']] : []);

        foreach ($destinatarios as $u) {
            $uid = (int)$u['id'];
            // notificación in-app
            $this->notifModel->crear(
                $uid,
                'alerta',
                $titulo,
                resumen($cuerpo ?: $titulo, 140),
                $link,
                ['alerta_id'=>(int)$aid]
            );

            // email si procede y el usuario lo permite
            if ($enviar && !empty($u['email']) && (int)($u['notif_email_alertas'] ?? 1) === 1) {
                $panel = (strtolower($u['rol'] ?? '') === 'admin') ? 'Admin' : 'Usuario';
                $loginLink = BASE_URL . 'auth/login?next=' . urlencode(BASE_URL . $panel); // aterriza en panel
                [$subject, $html, $text] = buildAlertEmailTemplate(
                    $loginLink,
                    $titulo,
                    $cuerpo ?: null,
                    $link ?: null,
                    $u['nombre'] ?? $u['email'],
                    'logo-alert'
                );
                enviarCorreo($u['email'], $u['nombre'] ?? '', $subject, $html, $text, $embedded, []);
            }
        }

        $_SESSION['flash_ok'] = 'Alerta creada y enviada.';
        redir(BASE_URL.'AdminAlertas/crear');
    }
}

