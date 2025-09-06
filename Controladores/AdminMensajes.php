<?php
class AdminMensajes extends Controlador
{
    private ConversacionModel $convModel;
    private MensajeModel $msgModel;
    private UsuarioModel $usuarioModel;
    private MensajeAdjuntoModel $adjModel;


    public function __construct()
    {
        parent::__construct();
        requireAdmin();
        $this->convModel     = new ConversacionModel();
        $this->msgModel      = new MensajeModel();
        $this->usuarioModel  = new UsuarioModel();
        $this->adjModel      = new MensajeAdjuntoModel();
    }

    /** Lista conversaciones del admin (participante A o B) + selector y buscador */
    public function index(int $page = 1)
    {
        $adminId   = (int)($_SESSION['user']['id'] ?? 0);
        $per       = 20;
        $items     = $this->convModel->listarPorUsuario($adminId, $page, $per); // ya la tienes
        // top 5 usuarios sugeridos (ejemplo)
        $topUsers  = $this->usuarioModel->top5Excepto(5); // si no existe, crea un stub que devuelva 5 últimos
        $data = [
            'titulo'    => 'Mensajes',
            'csrf'      => csrfToken(),
            'items'     => $items,
            'hilo'      => [],   // vacío hasta abrir una conversación
            'topUsers'  => $topUsers,
            'to_id'     => null,
        ];
        $this->view('Admin/mensajes_index', $data); // reutilizamos la misma vista que ya has montado
    }

    public function ver(int $otroId)
    {
        $yo  = (int)$_SESSION['user']['id'];
        $cid = $this->convModel->obtenerOCrear($yo, (int)$otroId);

        $hilo = $this->msgModel->listarPorConversacion($cid, 500, 0);
        // Enriquecer con adjuntos (1 consulta por mensaje si prefieres agrupar ya en el controlador)
        if ($hilo) {
            $ids = array_column($hilo, 'id');
            $adjuntosPlano = $this->adjModel->obtenerPorMensajes($ids);
            $porMensaje = [];
            foreach ($adjuntosPlano as $a) {
                $porMensaje[(int)$a['mensaje_id']][] = $a;
            }
            foreach ($hilo as &$m) {
                $m['adjuntos'] = $porMensaje[(int)$m['id']] ?? [];
            }
            unset($m);
        }

        $this->convModel->marcarLeido($cid, $yo);

        $data = [
            'titulo'   => 'Mensajes',
            'csrf'     => csrfToken(),
            'topUsers' => $this->usuarioModel->top5Excepto($yo),
            'items'    => $this->convModel->listarPorUsuario($yo, 1, 20),
            'hilo'     => $hilo,
            'to_id'    => (int)$otroId,
        ];
        $this->view('Admin/mensajes_index', $data);
    }

    public function enviarPost()
    {
        // Siempre JSON
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        try {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
                return;
            }
            $csrf = $_POST['csrf'] ?? '';
            if (!verificarCsrf($csrf)) {
                echo json_encode(['ok' => false, 'error' => 'CSRF inválido']);
                return;
            }

            $yo     = (int)($_SESSION['user']['id'] ?? 0);
            $to     = (int)($_POST['to_id'] ?? 0);
            $cuerpo = trim((string)($_POST['body'] ?? '')) ?: null;
            if (!$yo || !$to) {
                echo json_encode(['ok' => false, 'error' => 'Falta destinatario']);
                return;
            }

            $cid = $this->convModel->obtenerOCrear($yo, $to);
            $mid = $this->msgModel->crear($cid, $yo, $cuerpo);
            if (!$mid) {
                echo json_encode(['ok' => false, 'error' => 'No se pudo crear el mensaje']);
                return;
            }

            // Subir adjuntos a Assets/img/mensajes
            $guardados = $this->subirAdjuntos($mid, $_FILES['files'] ?? null, [
                'subdir'   => 'mensajes',
                'maxMB'    => 150,
                'destBase' => rtrim(BASE_PATH, '/\\') . '/Assets/img', // ← IMÁGENES/VÍDEOS AQUÍ
            ]);
            // Notificación para el receptor ($to)
            $notifModel =  new NotificacionModel();
            $dest = $this->usuarioModel->buscarPorId($to);

            $texto = $cuerpo ? resumen($cuerpo, 80) : 'Has recibido un nuevo mensaje';
            // Si el receptor es admin, abrimos su panel de admin, si no, el de usuario
            $esAdmin = (strtolower($dest['rol'] ?? '') === 'admin');
            $baseRuta = $esAdmin ? 'AdminMensajes/ver/' : 'UsuarioMensajes/ver/';
            // El "ver" debe abrir la conversación con el REMITENTE ($yo)
            $link = BASE_URL . $baseRuta . (int)$yo;

            $notifModel->crear(
                (int)$to,
                'mensaje',
                'Nuevo mensaje',
                $texto,
                $link,
                ['conversacion_id' => (int)$cid, 'mensaje_id' => (int)$mid, 'remitente_id' => (int)$yo]
            );
            $this->convModel->tocarTrasMensaje($cid, $yo);
            // ===== Email al destinatario =====
            try {
                if (!empty($dest['email']) && (int)($dest['notif_email_mensajes'] ?? 1) === 1) {
                    $convUrl   = $link; // ya apunta a ver/$yo en el panel correcto
                    $loginLink = BASE_URL . 'auth/login?next=' . urlencode($convUrl);
                    $snippet   = $cuerpo ? resumen($cuerpo, 120) : null;

                    $remNombre = $_SESSION['user']['nombre'] ?? 'Un usuario';
                    [$subject, $html, $text] = buildNewMessageEmailTemplate(
                        $loginLink,
                        $dest['nombre'] ?? $dest['email'] ?? '',
                        $remNombre,
                        $snippet,
                        'logo-msg'
                    );

                    $embedded = [];
                    $logoPath = rtrim(BASE_PATH, '/\\') . '/Assets/img/logo-ajedrez.png';
                    if (is_file($logoPath)) {
                        $embedded[] = ['path' => $logoPath, 'cid' => 'logo-msg', 'name' => 'logo.png', 'type' => 'image/png'];
                    }

                    enviarCorreo(
                        $dest['email'],
                        $dest['nombre'] ?? '',
                        $subject,
                        $html,
                        $text,
                        $embedded,
                        []
                    );
                }
            } catch (Throwable $e) {
                error_log('Aviso email nuevo mensaje: ' . $e->getMessage());
            }
            $mensaje = $this->msgModel->obtenerPorId((int)$mid);
            if (!$mensaje) {
                // fallback si por cualquier motivo aún no se ve el insert
                $mensaje = [
                    'id'              => (int)$mid,
                    'conversacion_id' => (int)$cid,
                    'remitente_id'    => (int)$yo,
                    'cuerpo'          => $cuerpo,
                    'created_at'      => date('Y-m-d H:i:s'),
                ];
            }

            // Añade datos del remitente para el parcial
            $mensaje['nombre']   = $_SESSION['user']['nombre']   ?? 'Yo';
            $mensaje['email']    = $_SESSION['user']['email']    ?? '';
            $mensaje['foto_url'] = $_SESSION['user']['foto_url'] ?? null;

            // Adjuntos que acabas de subir
            $adjuntos = $guardados;

            // Render del parcial con variables listas
            ob_start();
            $tpl = rtrim(BASE_PATH, '/\\') . '/Views/Admin/mensaje_item.php';
            if (!is_file($tpl)) throw new RuntimeException('Falta plantilla: ' . $tpl);
            include $tpl;
            $html = ob_get_clean();

            echo json_encode(['ok' => true, 'html' => $html, 'last_id' => (int)$mid]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }
    // ---------------------------------------------------------
    // Subida de archivos (imágenes/vídeos) — LÓGICA EN CONTROLADOR
    // ---------------------------------------------------------
    private function subirAdjuntos(int $mensajeId, ?array $files, array $opts): array
    {
        if (!$files) return [];

        $subdir   = trim((string)($opts['subdir'] ?? 'mensajes'), '/\\');
        $maxMB    = (int)($opts['maxMB'] ?? 15);
        $destBase = rtrim((string)($opts['destBase'] ?? (rtrim(BASE_PATH, '/\\') . '/Assets/img')), '/\\');

        $destAbs  = $destBase . '/' . $subdir . '/';
        if (!is_dir($destAbs)) @mkdir($destAbs, 0775, true);

        $allowedExt  = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'mov', 'm4v', 'mp3', 'wav', 'ogg', 'm4a', 'aac'];
        $allowedMime = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/gif',
            'video/mp4',
            'video/webm',
            'video/quicktime',
            'audio/mpeg',
            'audio/wav',
            'audio/ogg',
            'audio/mp4',
            'audio/aac'
        ];
        $maxBytes = $maxMB * 1024 * 1024;

        // Normaliza single/multiple
        $items = [];
        if (is_array($files['name'])) {
            $N = count($files['name']);
            for ($i = 0; $i < $N; $i++) {
                $items[] = [
                    'name'     => $files['name'][$i] ?? '',
                    'type'     => $files['type'][$i] ?? '',
                    'tmp_name' => $files['tmp_name'][$i] ?? '',
                    'error'    => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                    'size'     => $files['size'][$i] ?? 0,
                ];
            }
        } else {
            $items[] = [
                'name'     => $files['name']     ?? '',
                'type'     => $files['type']     ?? '',
                'tmp_name' => $files['tmp_name'] ?? '',
                'error'    => $files['error']    ?? UPLOAD_ERR_NO_FILE,
                'size'     => $files['size']     ?? 0,
            ];
        }

        $guardados = [];

        foreach ($items as $f) {
            if ((int)($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
            $tmp  = (string)($f['tmp_name'] ?? '');
            $name = (string)($f['name'] ?? '');
            $size = (int)($f['size'] ?? 0);
            if (!$tmp || !is_uploaded_file($tmp) || $size <= 0 || $size > $maxBytes) continue;

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmp) ?: 'application/octet-stream';
            finfo_close($finfo);

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt, true)) continue;
            if (!in_array(strtolower($mime), $allowedMime, true)) continue;

            $tipo = 'archivo';
            if (strpos($mime, 'image/') === 0) $tipo = 'imagen';
            elseif (strpos($mime, 'video/') === 0) $tipo = 'video';
            elseif (strpos($mime, 'audio/') === 0) $tipo = 'audio';
            if ($tipo === 'archivo') continue;

            $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($name, PATHINFO_FILENAME));
            $base = preg_replace('/\\.(php|phtml|phar|htaccess|ini|sh|bat)$/i', '', $base);
            $final = $base . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest  = $destAbs . $final;

            if (!@move_uploaded_file($tmp, $dest)) continue;

            $ancho = $alto = null;
            if ($tipo === 'imagen') {
                $info = @getimagesize($dest);
                if ($info) {
                    $ancho = (int)($info[0] ?? 0);
                    $alto = (int)($info[1] ?? 0);
                }
            }

            $rutaRel = 'Assets/img/' . $subdir . '/' . $final;

            $idAdj = $this->adjModel->crear($mensajeId, $tipo, $rutaRel, $mime, $size, $ancho, $alto, null);
            if ($idAdj) {
                $guardados[] = [
                    'id'        => (int)$idAdj,
                    'mensaje_id' => $mensajeId,
                    'tipo'      => $tipo,
                    'ruta'      => $rutaRel,
                    'mime'      => $mime,
                    'tamanio'   => $size,
                    'ancho'     => $ancho,
                    'alto'      => $alto,
                    'duracion'  => null,
                ];
            } else {
                @unlink($dest);
            }
        }

        return $guardados;
    }
    /** Buscador de usuarios para el selector (JSON) */
    public function buscarUsuarios()
    {
        header('Content-Type: application/json; charset=utf-8');
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode(['ok' => true, 'items' => []]);
            return;
        }
        $items = $this->usuarioModel->buscarPorTexto($q, 10);
        echo json_encode(['ok' => true, 'items' => $items]);
    }
}
