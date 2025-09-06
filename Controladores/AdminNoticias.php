<?php
class AdminNoticias extends Controlador
{


    private MedioModel $medioModel;           // para tabla `medios`
    private NoticiaMediaModel $linkModel;


    public function __construct()
    {
        parent::__construct();
        requireLogin();
        requireAdmin();
        $this->model = new NoticiasModel();
        $this->medioModel = new MedioModel();
        $this->linkModel = new NoticiaMediaModel();
    }


    // GET /AdminNoticias/index[/<page>]
    public function index(int $pagina = 1): void
    {
        $estado = $_GET['estado'] ?? '';
        $estado = in_array($estado, ['borrador', 'publicado'], true) ? $estado : null;
        $categoria = $_GET['categoria'] ?? '';
        $categoria = in_array($categoria, ['club', 'ajedrez', 'escuela'], true) ? $categoria : null;
        $q = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($q) > 120) $q = mb_substr($q, 0, 120);
        $orden = $_GET['orden'] ?? 'recientes';
        $per = (int)($_GET['per'] ?? 12);
        $per = in_array($per, [12, 18, 24, 36], true) ? $per : 12;


        $res = $this->model->listarAdmin($estado, $categoria, max(1, $pagina), $per, $q, $orden);
        $data = ['titulo' => 'Noticias — Admin', 'csrf' => csrfToken(), 'items' => $res['items'], 'f_estado' => $estado ?? '', 'f_categoria' => $categoria ?? '', 'q' => $q, 'orden' => $orden, 'meta' => ['page' => $res['page'], 'per' => $res['per'], 'total' => $res['total'], 'total_pages' => $res['total_pages']], 'base_url' => BASE_URL . 'AdminNoticias/index/'];
        $this->view('Admin/Noticias/lista', $data);
    }


    // GET /AdminNoticias/crear
    public function crear(): void
    {
        $data = ['titulo' => 'Crear noticia', 'csrf' => csrfToken(), 'n' => ['titulo' => '', 'categoria' => 'club', 'resumen' => '', 'contenido' => '', 'portada' => null, 'estado' => 'borrador']];
        $this->view('Admin/Noticias/form', $data);
    }


    // POST /AdminNoticias/guardar
    public function guardar(): void
    {
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminNoticias/index');
        }
        $titulo = limpiar($_POST['titulo'] ?? '');
        $slug = slugify($titulo);
        // Forzar slug único
        $i = 2;
        $base = $slug;
        while (!$this->model->slugDisponible($slug)) {
            $slug = $base . '-' . $i++;
        }
        $categoria = in_array(($_POST['categoria'] ?? ''), ['club', 'ajedrez', 'escuela'], true) ? $_POST['categoria'] : 'club';
        $resumen = trim((string)($_POST['resumen'] ?? ''));
        $contenido = (string)($_POST['contenido'] ?? ''); // contenido puede incluir HTML (admin confiable)
        $estado = isset($_POST['publicar']) ? 'publicado' : 'borrador';
        $publicado_at = ($estado === 'publicado') ? date('Y-m-d H:i:s') : null;
        $portada = null;


        if (!empty($_FILES['portada']['name'])) {
            [$ok, $res] = uploadImage($_FILES['portada'], 'noticias', ['jpg', 'jpeg', 'png', 'webp'], 6);
            if ($ok) {
                $portada = 'Assets/img/noticias/' . $res;
            }
        }


        $id = $this->model->crear([
            'titulo' => $titulo,
            'slug' => $slug,
            'categoria' => $categoria,
            'resumen' => $resumen,
            'contenido' => $contenido,
            'portada' => $portada,
            'autor_id' => ($_SESSION['user']['id'] ?? null),
            'estado' => $estado,
            'publicado_at' => $publicado_at
        ]);


        // Adjuntar medios (opcional, múltiples)
        if (
            $id
            && isset($_FILES['medios'])
            && is_array($_FILES['medios']['name'] ?? null)
            && array_filter($_FILES['medios']['name'] ?? [], fn($n) => (string)$n !== '')
        ) {
            $this->procesarMedios((int)$id, $_FILES['medios']);
        }


        $_SESSION['flash_ok'] = $id ? 'Noticia creada' : 'No se pudo crear';
        redir(BASE_URL . 'AdminNoticias/index');
    }
    // POST /AdminNoticias/actualizar/<id>
    public function actualizar(int $id): void
    {
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminNoticias/index');
        }

        $n = $this->model->buscarPorId($id);
        if (!$n) {
            redir(BASE_URL . 'AdminNoticias/index');
        }

        // Campos principales
        $titulo = limpiar($_POST['titulo'] ?? '');
        $slug   = slugify($titulo);
        // Forzar slug único (excluyendo la noticia actual)
        $i = 2;
        $base = $slug;
        while (!$this->model->slugDisponible($slug, $id)) {
            $slug = $base . '-' . $i++;
        }

        $categoria = in_array(($_POST['categoria'] ?? ''), ['club', 'ajedrez', 'escuela'], true) ? $_POST['categoria'] : 'club';
        $resumen   = trim((string)($_POST['resumen'] ?? ''));
        $contenido = (string)($_POST['contenido'] ?? '');
        $estado    = isset($_POST['publicar']) ? 'publicado' : 'borrador';
        $publicado_at = ($estado === 'publicado') ? ($n['publicado_at'] ?: date('Y-m-d H:i:s')) : null;

        // Portada (reemplazo opcional)
        $portada = $n['portada'] ?? null;
        if (!empty($_FILES['portada']['name'])) {
            [$ok, $res] = uploadImage($_FILES['portada'], 'noticias', ['jpg', 'jpeg', 'png', 'webp'], 6);
            if ($ok) {
                if (!empty($portada)) {
                    $old = basename($portada);
                    deleteFile($old, 'noticias'); // elimina la anterior
                }
                $portada = 'Assets/img/noticias/' . $res;
            }
        }

        // Guardar cambios
        $this->model->actualizar($id, [
            'titulo'       => $titulo,
            'slug'         => $slug,
            'categoria'    => $categoria,
            'resumen'      => $resumen,
            'contenido'    => $contenido,
            'portada'      => $portada,
            'estado'       => $estado,
            'publicado_at' => $publicado_at
        ]);

        // Adjuntar nuevos medios si realmente hay archivos
        if (
            $id
            && isset($_FILES['medios'])
            && is_array($_FILES['medios']['name'] ?? null)
            && array_filter($_FILES['medios']['name'] ?? [], fn($n) => (string)$n !== '')
        ) {
            $this->procesarMedios((int)$id, $_FILES['medios']);
        }

        $_SESSION['flash_ok'] = 'Cambios guardados';
        redir(BASE_URL . 'AdminNoticias/editar/' . $id);
    }


    // GET /AdminNoticias/editar/<id>
    public function editar(int $id): void
    {
        $n = $this->model->buscarPorId($id);
        if (!$n) {
            redir(BASE_URL . 'AdminNoticias/index');
        }
        $gal = $this->linkModel->listarPorNoticia($id);
        $data = ['titulo' => 'Editar noticia', 'csrf' => csrfToken(), 'n' => $n, 'galeria' => $gal];
        $this->view('Admin/Noticias/form', $data);
    }
    public function ordenarMediosAjax(int $noticiaId): void
    {
        requireLogin();
        requireRole('admin');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            exit;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        $csrf = $data['csrf'] ?? '';
        if (!verificarCsrf($csrf)) {
            http_response_code(403);
            echo json_encode(['ok' => false, 'msg' => 'CSRF']);
            return;
        }

        $orden = $data['orden'] ?? [];
        if (!is_array($orden)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'msg' => 'Formato inválido']);
            return;
        }

        $pos = 1;
        foreach ($orden as $medioId) {
            $this->linkModel->setOrden($noticiaId, (int)$medioId, $pos++);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
    }
    private function procesarMedios(int $noticiaId, array $files): void
    {
        if (!isset($files['name']) || !is_array($files['name'])) return;

        $count = count($files['name']);
        $destDir = rtrim(BASE_PATH, '/\\') . '/Assets/media/noticias/';
        if (!is_dir($destDir)) {
            @mkdir($destDir, 0775, true);
        }

        $fi = class_exists('finfo') ? new finfo(FILEINFO_MIME_TYPE) : null;
        $allow = [
            'imagen' => ['jpg', 'jpeg', 'png', 'webp'],
            'video'  => ['mp4', 'mov', 'mkv', 'webm'],
            'audio'  => ['mp3', 'wav', 'ogg', 'm4a'],
        ];

        for ($i = 0; $i < $count; $i++) {
            if (($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

            $tmp  = $files['tmp_name'][$i] ?? '';
            $name = $files['name'][$i]     ?? '';
            $size = (int)($files['size'][$i] ?? 0);
            if (!$tmp || !is_uploaded_file($tmp)) continue;

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $mime = $fi ? $fi->file($tmp) : (@mime_content_type($tmp) ?: null);
            if (!$mime) {
                $map = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'mp4' => 'video/mp4', 'mov' => 'video/quicktime', 'mkv' => 'video/x-matroska', 'webm' => 'video/webm', 'mp3' => 'audio/mpeg', 'wav' => 'audio/wav', 'ogg' => 'audio/ogg', 'm4a' => 'audio/mp4'];
                $mime = $map[$ext] ?? 'application/octet-stream';
            }

            $tipo = (strpos($mime, 'video/') === 0) ? 'video' : ((strpos($mime, 'audio/') === 0) ? 'audio' : 'imagen');
            if (!in_array($ext, $allow[$tipo], true)) continue;

            $base = preg_replace('/[^a-zA-Z0-9_-]+/', '-', pathinfo($name, PATHINFO_FILENAME));
            $safe = $base . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

            if (!@move_uploaded_file($tmp, $destDir . $safe)) continue;

            $w = null;
            $h = null;
            if ($tipo === 'imagen') {
                $inf = @getimagesize($destDir . $safe);
                if ($inf) {
                    $w = $inf[0] ?? null;
                    $h = $inf[1] ?? null;
                }
            }

            $rel = 'Assets/media/noticias/' . $safe;
            $medioId = $this->medioModel->crear([
                'filename' => $safe,
                'path' => $rel,
                'mime' => $mime,
                'width' => $w,
                'height' => $h,
                'size' => $size,
                'alt_text' => $base,
                'subido_por' => ($_SESSION['user']['id'] ?? null)
            ]);
            if ($medioId) {
                $this->linkModel->adjuntar($noticiaId, $medioId, $tipo, 0);
            }
        }
    }
    // POST /AdminNoticias/borrar/<id>
    public function borrar(int $id): void
    {
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminNoticias/index');
        }

        $n = $this->model->buscarPorId($id);
        if (!$n) {
            $_SESSION['flash_error'] = 'Noticia no encontrada';
             redir(BASE_URL . 'AdminNoticias/index');
        }

        // 1) Portada
        if (!empty($n['portada'])) {
            $old = basename($n['portada']); // ej. Assets/img/noticias/xxx.webp -> xxx.webp
            deleteFile($old, 'noticias');   // usa tu helper
        }

        // 2) Guardar IDs de medios asociados (antes de borrar la noticia)
        $gal = $this->linkModel->listarPorNoticia($id);
        $medioIds = [];
        foreach ($gal as $m) {
            $medioIds[(int)$m['medio_id']] = true; // set único
        }
        $medioIds = array_keys($medioIds);

        // 3) Borrar la noticia (los enlaces en noticias_medios se borran por FK ON DELETE CASCADE)
        $this->model->borrar($id);

        // 4) Para cada medio: si no está ya vinculado a otra noticia, borrar archivo + registro
        foreach ($medioIds as $medioId) {
            // ¿quedan vínculos a este medio?
            if ($this->linkModel->contarVinculos($medioId) === 0) {
                $media = $this->medioModel->obtener($medioId); // o $this->mediaModel
                if ($media && !empty($media['path'])) {
                    $abs = BASE_PATH . ltrim($media['path'], '/\\');
                    if (is_file($abs)) {
                        @unlink($abs);
                    }
                }
                $this->medioModel->borrarUno($medioId); // firma: borrarUno(int $id)
            }
        }

        $_SESSION['flash_ok'] = 'Noticia eliminada';
        redir(BASE_URL . 'AdminNoticias/index');
    }
}
