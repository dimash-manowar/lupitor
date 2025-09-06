<?php
class Notificaciones extends Controlador
{
    private NotificacionesModel $notif;

    public function __construct()
    {
        parent::__construct();
        requireLogin();
        $this->notif = new NotificacionesModel();
    }

    /** GET /Notificaciones/count -> {ok:true, count:n} */
     public function count() {
        header('Content-Type: application/json; charset=utf-8');
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        echo json_encode(['ok'=>true,'count'=>$this->notif->contarNoLeidas($uid)]);
    }

    // RENOMBRADO: antes “list”
    public function listar(int $limit = 10) {
        header('Content-Type: application/json; charset=utf-8');
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        echo json_encode(['ok'=>true,'items'=>$this->notif->listarRecientes($uid, $limit)]);
    }

    /** POST /Notificaciones/marcarLeida */
    public function marcarLeida()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            echo json_encode(['ok' => false, 'error' => 'CSRF inválido']);
            return;
        }
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $id  = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            return;
        }
        $n = $this->notif->marcarLeida($id, $uid);
        echo json_encode(['ok' => true, 'done' => $n > 0]);
    }

    /** POST /Notificaciones/marcarTodas */
    public function marcarTodas()
    {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
            return;
        }
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            echo json_encode(['ok' => false, 'error' => 'CSRF inválido']);
            return;
        }
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $n = $this->notif->marcarTodasLeidas($uid);
        echo json_encode(['ok' => true, 'done' => $n]);
    }
    public function panel()
    {
        requireLogin();
        $items = $this->notif->listarRecientes((int)$_SESSION['user']['id'], 50);
        $this->view('Notificaciones/index', ['items' => $items, 'titulo' => 'Notificaciones']);
    }
}
