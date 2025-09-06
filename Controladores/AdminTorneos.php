<?php
class AdminTorneos extends Controlador
{


    private TorneoInscripcionModel $insModel;

    public function __construct()
    {
        parent::__construct();
        requireAdmin();
        $this->model = new TorneosModel();
        $this->insModel = new TorneoInscripcionModel();
    }

    public function index(int $pagina = 1): void
    {
        $estado = $_GET['estado'] ?? '';
        $modalidad = $_GET['modalidad'] ?? '';
        $q = trim((string)($_GET['q'] ?? ''));
        $orden = $_GET['orden'] ?? 'recientes';
        $per = (int)($_GET['per'] ?? 12);
        $per = in_array($per, [6, 9, 12, 18, 24], true) ? $per : 12;
        // arriba del método (junto con estado, modalidad, etc.)
        $federado = isset($_GET['federado']) ? trim((string)$_GET['federado']) : '';
        // admite '', '0' o '1'
        if ($federado !== '' && !in_array($federado, ['0', '1'], true)) $federado = '';
        $r = $this->model->listarAdmin($estado ?: null, $modalidad ?: null, max(1, $pagina), $per, $q ?: null, $orden);
        $data = array_merge(['r' => $r], $r, ['titulo' => 'Torneos', 'csrf' => csrfToken(), 'f' => compact('estado', 'modalidad', 'q', 'orden', 'per', 'federado')]);
        $this->view('Admin/torneos-index', $data);
    }

    public function crear(): void
    {
        $data = ['titulo' => 'Nuevo torneo', 'csrf' => csrfToken()];
        $this->view('Admin/torneos-form', $data);
    }

    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminTorneos/index');
        }
        $titulo = trim($_POST['titulo'] ?? '');
        $slug = slugify($titulo);
        $i = 2;
        $base = $slug;
        while (!$this->model->slugDisponible($slug)) $slug = $base . '-' . $i++;

        $d = [
            'titulo'      => $titulo,
            'slug'        => $slug,
            'modalidad'   => in_array($_POST['modalidad'] ?? 'otro', ['clásico', 'rápidas', 'blitz', 'escolar', 'otro'], true) ? $_POST['modalidad'] : 'otro',
            'inicio'      => ($_POST['inicio'] ?? '') ?: date('Y-m-d 10:00:00'),
            'fin'         => ($_POST['fin'] ?? '') ?: null,
            'lugar'       => trim($_POST['lugar'] ?? ''),
            'precio'      => (float)($_POST['precio'] ?? 0),
            'cupo'        => $_POST['cupo'] !== '' ? (int)$_POST['cupo'] : null,
            'resumen'     => trim((string)($_POST['resumen'] ?? '')),
            'descripcion' => (string)($_POST['descripcion'] ?? ''),
            'estado'      => in_array($_POST['estado'] ?? 'publicado', ['borrador', 'publicado', 'cancelado'], true) ? $_POST['estado'] : 'publicado',
            'form_activo' => isset($_POST['form_activo']) ? 1 : 0,
            'bases_pdf'   => null,
            'portada'     => null,
            'creado_por'  => $_SESSION['user']['id'] ?? null
        ];

        if (!empty($_FILES['portada']['name'])) {
            [$ok, $res] = uploadImage($_FILES['portada'], 'torneos', ['jpg', 'jpeg', 'png', 'webp'], 6);
            if ($ok) $d['portada'] = 'Assets/img/torneos/' . $res;
        }
        if (!empty($_FILES['bases_pdf']['name'])) {
            // Sube a /Assets/docs/torneos/
            if (!defined('BASE_PATH')) define('BASE_PATH', __DIR__ . '/../');
            $dir = rtrim(BASE_PATH, '/\\') . '/Assets/docs/torneos/';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $ext = strtolower(pathinfo($_FILES['bases_pdf']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf'], true)) {
                $final = 'bases-' . bin2hex(random_bytes(4)) . '.pdf';
                if (@move_uploaded_file($_FILES['bases_pdf']['tmp_name'], $dir . $final)) {
                    $d['bases_pdf'] = 'Assets/docs/torneos/' . $final;
                }
            }
        }

        $id = $this->model->crear($d);
        $_SESSION['flash_ok'] = $id ? 'Torneo creado' : 'No se pudo crear';
        redir(BASE_URL . 'AdminTorneos/index');
    }

    public function editar(int $id): void
    {
        $t = $this->model->buscarPorId($id);
        if (!$t) {
            $_SESSION['flash_error'] = 'No encontrado';
            redir(BASE_URL . 'AdminTorneos/index');
        }
        $data = ['titulo' => 'Editar torneo', 'csrf' => csrfToken(), 't' => $t];
        $this->view('Admin/torneos-form', $data);
    }

    public function actualizar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminTorneos/index');
        }
        $t = $this->model->buscarPorId($id);
        if (!$t) redir(BASE_URL . 'AdminTorneos/index');

        $titulo = trim($_POST['titulo'] ?? '');
        $slug = slugify($titulo);
        $i = 2;
        $base = $slug;
        while (!$this->model->slugDisponible($slug, $id)) $slug = $base . '-' . $i++;

        $d = [
            'titulo'      => $titulo,
            'slug'        => $slug,
            'modalidad'   => in_array($_POST['modalidad'] ?? 'otro', ['clásico', 'rápidas', 'blitz', 'escolar', 'otro'], true) ? $_POST['modalidad'] : 'otro',
            'inicio'      => ($_POST['inicio'] ?? '') ?: date('Y-m-d 10:00:00'),
            'fin'         => ($_POST['fin'] ?? '') ?: null,
            'lugar'       => trim($_POST['lugar'] ?? ''),
            'precio'      => (float)($_POST['precio'] ?? 0),
            'cupo'        => $_POST['cupo'] !== '' ? (int)$_POST['cupo'] : null,
            'resumen'     => trim((string)($_POST['resumen'] ?? '')),
            'descripcion' => (string)($_POST['descripcion'] ?? ''),
            'estado'      => in_array($_POST['estado'] ?? 'publicado', ['borrador', 'publicado', 'cancelado'], true) ? $_POST['estado'] : 'publicado',
            'form_activo' => isset($_POST['form_activo']) ? 1 : 0,
            'bases_pdf'   => $t['bases_pdf'] ?? null,
            'portada'     => $t['portada'] ?? null
        ];

        if (!empty($_FILES['portada']['name'])) {
            [$ok, $res] = uploadImage($_FILES['portada'], 'torneos', ['jpg', 'jpeg', 'png', 'webp'], 6);
            if ($ok) {
                if (!empty($d['portada'])) deleteFile(basename($d['portada']), 'torneos');
                $d['portada'] = 'Assets/img/torneos/' . $res;
            }
        }
        if (!empty($_FILES['bases_pdf']['name'])) {
            if (!defined('BASE_PATH')) define('BASE_PATH', __DIR__ . '/../');
            $dir = rtrim(BASE_PATH, '/\\') . '/Assets/docs/torneos/';
            if (!is_dir($dir)) @mkdir($dir, 0775, true);
            $ext = strtolower(pathinfo($_FILES['bases_pdf']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['pdf'], true)) {
                if (!empty($d['bases_pdf']) && is_file(BASE_PATH . $d['bases_pdf'])) @unlink(BASE_PATH . $d['bases_pdf']);
                $final = 'bases-' . bin2hex(random_bytes(4)) . '.pdf';
                if (@move_uploaded_file($_FILES['bases_pdf']['tmp_name'], $dir . $final)) {
                    $d['bases_pdf'] = 'Assets/docs/torneos/' . $final;
                }
            }
        }

        $this->model->actualizar($id, $d);
        $_SESSION['flash_ok'] = 'Torneo actualizado';
        redir(BASE_URL . 'AdminTorneos/editar/' . $id);
    }

    public function eliminar(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminTorneos/index');
        }
        $t = $this->model->buscarPorId($id);
        if ($t) {
            if (!empty($t['portada'])) deleteFile(basename($t['portada']), 'torneos');
            if (!empty($t['bases_pdf']) && is_file(BASE_PATH . $t['bases_pdf'])) @unlink(BASE_PATH . $t['bases_pdf']);
            $this->model->borrar($id);
        }
        $_SESSION['flash_ok'] = 'Torneo eliminado';
        redir(BASE_URL . 'AdminTorneos/index');
    }

    public function inscripciones(int $torneoId, int $pagina = 1): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $r = $this->insModel->listarPorTorneo((int)$torneoId, max(1, $pagina), 20, $q ?: null);
        $t = $this->model->buscarPorId($torneoId);
        $data = array_merge($r, ['titulo' => 'Inscripciones', 'csrf' => csrfToken(), 't' => $t, 'q' => $q]);
        $this->view('Admin/torneos-inscripciones', $data);
    }
}
