<?php
class AdminInscripciones extends Controlador
{


    public function __construct()
    {
        parent::__construct();
        requireAdmin();
        $this->model = new AdminInscripcionesModel();
    }

    public function index(int $pagina = 1): void
    {
        $torneo_id = isset($_GET['torneo_id']) && $_GET['torneo_id'] !== '' ? (int)$_GET['torneo_id'] : null;
        $estado    = $_GET['estado'] ?? '';
        $pago      = $_GET['pago'] ?? '';
        $q         = trim((string)($_GET['q'] ?? ''));
        $desde     = $_GET['desde'] ?? null;
        $hasta     = $_GET['hasta'] ?? null;
        $orden     = $_GET['orden'] ?? 'recientes';
        $per       = (int)($_GET['per'] ?? 20);
        $per       = in_array($per, [10, 20, 30, 50], true) ? $per : 20;

        $r = $this->model->listarAdmin($torneo_id, $estado ?: null, $pago ?: null, max(1, $pagina), $per, $q ?: null, $desde ?: null, $hasta ?: null, $orden);
        $stats = $this->model->contarPorEstado($torneo_id, $pago ?: null, $q ?: null, $desde ?: null, $hasta ?: null);
        $data = array_merge($r, [
            'titulo'  => 'Inscripciones',
            'csrf'    => csrfToken(),
            'f'       => compact('torneo_id', 'estado', 'pago', 'q', 'desde', 'hasta', 'orden', 'per'),
            'torneos' => $this->model->torneosOptions(),
            'stats'   => $stats, // 👈 pasa los contadores a la vista
        ]);
        $this->view('Admin/inscripciones-index', $data);
    }

    public function cambiarEstado(): void
    {
        requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok' => false]);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $estado = trim((string)($_POST['estado'] ?? ''));
        if (!in_array($estado, ['pendiente', 'confirmada', 'anulada'], true)) {
            echo json_encode(['ok' => false]);
            return;
        }
        $n = $this->model->update("UPDATE {$this->model->getTable()} SET estado=?, updated_at=NOW() WHERE id=?", [$estado, $id]);
        echo json_encode(['ok' => $n > 0]);
    }

    public function cambiarPago(): void
    {
        requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok' => false]);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $ok = (int)($_POST['ok'] ?? 0);
        $n = $this->model->update("UPDATE {$this->model->getTable()} SET pago_ok=?, updated_at=NOW() WHERE id=?", [$ok ? 1 : 0, $id]);
        echo json_encode(['ok' => $n > 0]);
    }

    public function checkin(): void
    {
        requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok' => false]);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $row = $this->model->select_one("SELECT estado, checkin_at FROM {$this->model->getTable()} WHERE id=?", [$id]);
        if (!$row) {
            echo json_encode(['ok' => false]);
            return;
        }
        if (!empty($row['checkin_at'])) {
            echo json_encode(['ok' => true, 'repeat' => 1]);
            return;
        }
        // Si no está anulada, lo dejamos confirmada al hacer check-in
        $nuevoEstado = (isset($row['estado']) && $row['estado'] === 'anulada') ? 'anulada' : 'confirmada';
        $n = $this->model->update(
            "UPDATE {$this->model->getTable()} SET checkin_at=NOW(), estado=?, updated_at=NOW() WHERE id=?",
            [$nuevoEstado, $id]
        );
        echo json_encode(['ok' => $n > 0]);
    }

    public function eliminar(): void
    {
        requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok' => false]);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $n = $this->model->delete("DELETE FROM {$this->model->getTable()} WHERE id=?", [$id]);
        echo json_encode(['ok' => $n > 0]);
    }

    public function reenviar(): void
    {
        requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            http_response_code(400);
            echo json_encode(['ok' => false]);
            return;
        }
        $id = (int)($_POST['id'] ?? 0);
        $r = $this->model->getReciboData($id); // trae i.* + t.titulo,t.inicio,t.lugar,t.slug,t.precio
        if (!$r || empty($r['email'])) {
            echo json_encode(['ok' => false]);
            return;
        }

        try {
            // QR inline (CID) opcional
            $qrData = BASE_URL . 'Inscripcion/presentar?token=' . $r['checkin_token'];
            $qrApi  = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrData) . '&choe=UTF-8';
            $qrPng  = @file_get_contents($qrApi);
            $embedded = [];
            if ($qrPng) $embedded[] = ['string' => $qrPng, 'cid' => 'qrMail', 'name' => 'qr.png', 'type' => 'image/png'];

            // variables para la plantilla
            $nombre = $r['nombre'];
            $torneo = $r['torneo'];
            $fecha  = date('d/m/Y H:i', strtotime($r['inicio']));
            $lugar  = $r['lugar'] ?? '';
            $precio = ((float)$r['precio'] > 0) ? '€' . number_format((float)$r['precio'], 2, ',', '.') : 'Gratuito';
            $pago_modo = $r['pago_modo'] ?? '';
            $pago_ref  = $r['pago_ref'] ?? '';
            $qr_cid    = $qrPng ? 'qrMail' : null;
            $qr_url    = BASE_URL . 'Inscripcion/qr?token=' . $r['checkin_token'];
            $gestion_url = BASE_URL . 'Torneos/ver/' . urlencode($r['slug'] ?? '');

            ob_start();
            require BASE_PATH . '/Views/emails/inscripcion-ok.php';
            $html = ob_get_clean();

            $attachments = [];
            if (function_exists('buildIcsEvent')) {
                $endIso = !empty($r['fin']) ? $r['fin'] : date('Y-m-d H:i:s', strtotime($r['inicio'] . ' +4 hours'));
                $ics = buildIcsEvent($r['torneo'], 'Torneo de ajedrez', $r['inicio'], $endIso, $lugar);
                $attachments[] = ['string' => $ics, 'name' => 'torneo.ics', 'type' => 'text/calendar; charset=UTF-8; method=PUBLISH'];
            }

            $ok = enviarCorreo($r['email'], $nombre, 'Inscripción confirmada — ' . $r['torneo'], $html, strip_tags($html), $embedded, $attachments);
            echo json_encode(['ok' => $ok ? true : false]);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            echo json_encode(['ok' => false]);
        }
    }    

    public function recibo(int $id): void
    {
        requireRole('admin');

        // Datos desde el modelo (sin SQL aquí)
        $r = $this->model->getReciboData($id);
        if (!$r) {
            http_response_code(404);
            exit('No encontrado');
        }

        // Render vista a string usando require (sin helper externo)
        $vars = ['r' => $r];
        ob_start();
        extract($vars, EXTR_OVERWRITE);
        require BASE_PATH . '/Views/Admin/Pdf/recibo.php';
        $html = ob_get_clean();

        Pdf::stream($html, 'recibo-' . (int)$r['id'] . '.pdf', 'A5', 'portrait', true);
    }

    public function exportPdf(): void
    {
        requireRole('admin');

        // Recoje filtros como en tu index
        $torneo_id = isset($_GET['torneo_id']) && $_GET['torneo_id'] !== '' ? (int)$_GET['torneo_id'] : null;
        $estado    = $_GET['estado'] ?? null;
        $pago      = $_GET['pago'] ?? null;
        $q         = trim((string)($_GET['q'] ?? '')) ?: null;
        $desde     = $_GET['desde'] ?? null;
        $hasta     = $_GET['hasta'] ?? null;
        $orden     = $_GET['orden'] ?? 'recientes';

        // Datos desde el modelo (sin SQL aquí)
        $items = $this->model->listarParaPdf($torneo_id, $estado ?: null, $pago ?: null, $q, $desde, $hasta, $orden);

        // Render vista a string
        $vars = ['items' => $items];
        ob_start();
        extract($vars, EXTR_OVERWRITE);
        require BASE_PATH . '/Views/Admin/Pdf/listado-inscripciones.php';
        $html = ob_get_clean();
        if (!class_exists('Pdf')) {
            require_once BASE_PATH . '/Core/Pdf.php';
        }

        Pdf::stream($html, 'inscripciones.pdf', 'A4', 'portrait', true);
    }
}
