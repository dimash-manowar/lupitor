<?php
class Torneos extends Controlador
{

    private TorneoInscripcionModel $insModel;
    private TorneosModel $torneosModel;

    public function __construct()
    {
        parent::__construct();
        $this->model   = new TorneosModel();
        $this->insModel = new TorneoInscripcionModel();
        $this->torneosModel = new TorneosModel();
    }

    // GET /Torneos/index[/<page>]?modalidad=&q=&orden=&per=&desde=&hasta[&ajax=1]
    public function index(int $pagina = 1): void
    {
        $modalidad = $_GET['modalidad'] ?? '';
        $modalidad = in_array($modalidad, ['clásico', 'rápidas', 'blitz', 'escolar', 'otro'], true) ? $modalidad : '';
        $q     = trim((string)($_GET['q'] ?? ''));
        $orden = $_GET['orden'] ?? 'proximos';
        $per   = (int)($_GET['per'] ?? 9);
        $per = in_array($per, [6, 9, 12, 18, 24], true) ? $per : 9;
        $desde = $_GET['desde'] ?? '';
        $hasta = $_GET['hasta'] ?? '';

        $r = $this->model->listarPublico($modalidad ?: null, max(1, $pagina), $per, $q ?: null, $orden, $desde ?: null, $hasta ?: null);

        $data = [
            'titulo'     => 'Torneos',
            'hideNavbar' => true,
            'items'      => $r['items'],
            'meta'       => ['page' => $r['page'], 'per' => $r['per'], 'total' => $r['total'], 'total_pages' => $r['total_pages']],
            'modalidad'  => $modalidad,
            'q'          => $q,
            'orden'      => $orden,
            'per'        => $per,
            'desde'      => $desde,
            'hasta'      => $hasta,
            'base_url'   => BASE_URL . 'Torneos/index/',
        ];

        $esAjax = (isset($_GET['ajax']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');
        if ($esAjax) {
            $items = $data['items'];
            ob_start();
            require BASE_PATH . 'Views/Torneos/_cards.php';
            $htmlCards = ob_get_clean();
            ob_start();
            require BASE_PATH . 'Views/Torneos/_paginacion.php';
            $htmlPag = ob_get_clean();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok' => true,
                'cards' => $htmlCards,
                'pagination' => $htmlPag,
                'total' => $data['meta']['total'],
                'page' => $data['meta']['page'],
                'per' => $data['meta']['per']
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->view('Torneos/index', $data);
    }

    // GET /Torneos/ver/<slug>
    public function ver(string $slug): void
    {
        $n = $this->model->buscarPorSlug($slug);
        if (!$n || $n['estado'] !== 'publicado') {
            header('Location: ' . BASE_URL . 'Torneos');
            return;
        }

        $data = ['titulo' => $n['titulo'], 'hideNavbar' => true, 't' => $n, 'csrf' => csrfToken()];
        $this->view('Torneos/ver', $data);
    }

    // POST /Torneos/inscribirsePost
    public function inscribirsePost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['flash_error'] = 'Solicitud inválida.';
            header('Location: ' . BASE_URL);
            exit;
        }

        $tid  = (int)($_POST['torneo_id'] ?? 0);
        $t    = $this->torneosModel->buscarPorId($tid);
        if (!$t || empty($t['form_activo'])) {
            $_SESSION['flash_error'] = 'Torneo no disponible para inscripciones.';
            header('Location: ' . BASE_URL);
            exit;
        }

        // ===== Campos =====
        $f = fn($k) => trim((string)($_POST[$k] ?? ''));
        $nombre     = mb_substr($f('nombre'), 0, 100);
        $apellidos  = mb_substr($f('apellidos'), 0, 150);
        $direccion  = mb_substr($f('direccion'), 0, 200);
        $fecha_nac  = $f('fecha_nac') ?: null;
        $elo        = $f('elo') !== '' ? (int)$f('elo') : null;
        $email      = mb_substr($f('email'), 0, 150);
        $telefono   = mb_substr($f('telefono'), 0, 50);
        $federado   = isset($_POST['federado']) ? (int)$_POST['federado'] : 0; // <-- nuevo

        $pago_ok_in   = (int)($_POST['pago_ok'] ?? 0);
        $pago_modo_in = $f('pago_modo') ?: 'ninguno';
        $pago_ref     = mb_substr($f('pago_ref'), 0, 120) ?: null;

        $errores = [];
        if ($nombre === '') $errores[] = 'El nombre es obligatorio.';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email inválido.';

        $precio = (float)($t['precio'] ?? 0);

        // Normaliza pago según precio
        if ($precio > 0) {
            if (!$pago_ok_in) $errores[] = 'Debes completar el pago.';
            if ($pago_modo_in === 'ninguno') $errores[] = 'Falta el método de pago.';
        } else {
            // Torneo gratuito => se considera pagado en modo "gratis"
            $pago_ok_in   = 1;
            $pago_modo_in = 'gratis';
            $pago_ref     = $pago_ref ?: 'FREE';
        }

        // Cupo (si existe)
        if (!empty($t['cupo'])) {
            $inscritos = $this->insModel->contarPorTorneo($tid);
            if ($inscritos >= (int)$t['cupo']) $errores[] = 'No quedan plazas disponibles.';
        }

        if (!empty($errores)) {
            $_SESSION['flash_error'] = implode(' | ', $errores);
            header('Location: ' . BASE_URL . 'Torneos/ver/' . urlencode($t['slug']));
            exit;
        }
        $estado = 'pendiente';
        if ($precio == 0) { // gratuitos
            $pago_ok_in = 1;
            $pago_modo_in = 'gratis';
            $pago_ref = $pago_ref ?: 'FREE';
            $estado = 'confirmada';
        } elseif ($pago_ok_in) {
            $estado = 'confirmada';
        }
        // ===== Generar token de check-in (para QR) =====
        $checkin_token = bin2hex(random_bytes(16)); // 32 hex
        $federado   = isset($_POST['federado']) ? (int)$_POST['federado'] : 0;
        $userId = $_SESSION['user']['id'] ?? null;
        // ===== Guardar inscripción =====
        $id = $this->insModel->crear([
            'torneo_id'     => $tid,
            'user_id'       => $userId,
            'nombre'        => $nombre,
            'apellidos'     => $apellidos,
            'direccion'     => $direccion,
            'fecha_nac'     => $fecha_nac,
            'elo'           => $elo,
            'federado'      => $federado,
            'email'         => $email,
            'telefono'      => $telefono,                    // <-- nuevo
            'pago_modo'     => $pago_modo_in,
            'pago_ref'      => $pago_ref,
            'pago_ok'       => $pago_ok_in,       // usa el valor real (no fuerces 1 si no procede)
            'estado'        => $estado,      // ⚠️ coherente con filtros: pendiente|confirmada|anulada
            'checkin_token' => $checkin_token     // <-- nuevo
        ]);

        if ($id) {
            // ===== Enviar email de confirmación con QR (+ .ics opcional) =====
            try {
                $fechaStr    = date('d/m/Y H:i', strtotime($t['inicio']));
                $lugar       = $t['lugar'] ?? '';
                $precioNum   = (float)($t['precio'] ?? 0);
                $precioStr   = $precioNum > 0 ? ('€' . number_format($precioNum, 2, ',', '.')) : 'Gratuito';
                $gestion_url = BASE_URL . 'Torneos/ver/' . urlencode($t['slug']) . '?insc=1';
                $pago_modo   = $pago_modo_in;
                $pago_ref    = $pago_ref ?? '';

                // 1) Generar PNG del QR (vía Google Charts) y guardarlo en memoria
                $qrData = BASE_URL . 'Inscripcion/presentar?token=' . $checkin_token; // info dentro del QR
                $qrApi  = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($qrData) . '&choe=UTF-8';

                // descarga robusta (cURL fallback)
                $qrPng = @file_get_contents($qrApi);
                if ($qrPng === false) {
                    $ch = curl_init($qrApi);
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_TIMEOUT => 10,
                    ]);
                    $qrPng = curl_exec($ch);
                    curl_close($ch);
                }

                // 2) Render de la plantilla con variables
                $torneo = $t['titulo'];
                $nombreTpl = $nombre; // para claridad (mismo valor)
                $fecha  = $fechaStr;
                $precio = $precioStr;

                // si tenemos PNG, usaremos un CID:
                $qr_cid = $qrPng ? 'qrMail' : null;
                // también pasamos la URL por si algún cliente no soporta CID (fallback)
                $qr_url = BASE_URL . 'Inscripcion/qr?token=' . $checkin_token;

                ob_start();
                require BASE_PATH . '/Views/emails/inscripcion-ok.php';
                $html = ob_get_clean();

                $subject = 'Inscripción confirmada — ' . $t['titulo'];

                // 3) Adjuntos (ICS opcional) + embebido del QR
                $attachments = [];
                if (function_exists('buildIcsEvent')) {
                    $endIso = !empty($t['fin']) ? $t['fin'] : date('Y-m-d H:i:s', strtotime($t['inicio'] . ' +4 hours'));
                    $ics = buildIcsEvent($t['titulo'], 'Torneo de ajedrez', $t['inicio'], $endIso, $lugar);
                    $attachments[] = ['string' => $ics, 'name' => 'torneo.ics', 'type' => 'text/calendar; charset=UTF-8; method=PUBLISH'];
                }

                $embedded = [];
                if ($qrPng) {
                    // Embebemos el QR como imagen inline (CID)
                    $embedded[] = ['string' => $qrPng, 'cid' => 'qrMail', 'name' => 'qr.png', 'type' => 'image/png'];
                }

                enviarCorreo($email, $nombre, $subject, $html, strip_tags($html), $embedded, $attachments);
            } catch (\Throwable $e) {
                error_log('[inscribirsePost email] ' . $e->getMessage());
            }

            $_SESSION['flash_ok'] = 'Inscripción registrada correctamente.';
            header('Location: ' . BASE_URL . 'Torneos/ver/' . urlencode($t['slug']));
            exit;
        }

        $_SESSION['flash_error'] = 'No se pudo guardar la inscripción.';
        header('Location: ' . BASE_URL . 'Torneos/ver/' . urlencode($t['slug']));
        exit;
    }
}
