<?php
class AdminHomeCards extends Controlador
{


    public function __construct()
    {
        parent::__construct();
        requireLogin();
        requireAdmin();
        $this->model = new HomeCardModel();
    }

    public function index(): void
    {
        $data = [
            'titulo' => 'Tarjetas Home',
            'cards'  => $this->model->listar()
        ];
        $this->view('Admin/HomeCards/index', $data);
    }

    public function crear(): void
    {
        $data = [
            'titulo' => 'Crear tarjeta',
            'csrf'   => csrfToken(),
            'card'   => [
                'titulo' => '',
                'descripcion' => '',
                'icono' => 'bi-info-circle',
                'color_fondo' => '#0d6efd',
                'color_texto' => '#ffffff',
                'imagen' => null,
                'boton_texto' => 'Ver más',
                'destino' => '',
                'orden' => 0,
                'visible' => 1
            ]
        ];
        $this->view('Admin/HomeCards/form', $data);
    }

    public function guardar(): void
    {
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminHomeCards/index');
        }
        // Sanitizar
        $d = [
            'titulo'       => limpiar($_POST['titulo'] ?? ''),
            'descripcion'  => limpiar($_POST['descripcion'] ?? ''),
            'icono'        => limpiar($_POST['icono'] ?? ''),
            'color_fondo'  => limpiar($_POST['color_fondo'] ?? '#0d6efd'),
            validarColorHex('color_fondo') ?? '#0d6efd',
            'color_texto'  => limpiar($_POST['color_texto'] ?? '#ffffff'),
            validarColorHex('color_texto') ?? '#ffffff',
            'boton_texto'  => limpiar($_POST['boton_texto'] ?? 'Ver más'),
            'destino'      => trim($_POST['destino'] ?? ''),
            'orden'        => (int)($_POST['orden'] ?? 0),
            'visible'      => isset($_POST['visible']) ? 1 : 0,
            'imagen'       => null,
        ];

        // Imagen opcional
        if (!empty($_FILES['imagen']['name'])) {
            [$ok, $res] = uploadImage($_FILES['imagen'], 'home');
            if ($ok) $d['imagen'] = 'Assets/img/home/' . $res;
        }

        $id = $this->model->crear($d);
        $_SESSION['flash_ok'] = $id ? 'Tarjeta creada' : 'No se pudo crear';
        redir(BASE_URL . 'AdminHomeCards/index');
    }

    public function editar(int $id): void
    {
        $card = $this->model->find($id);
        if (!$card) {
            redir(BASE_URL . 'AdminHomeCards/index');
        }
        $data = [
            'titulo' => 'Editar tarjeta',
            'csrf'   => csrfToken(),
            'card'   => $card
        ];
        $this->view('Admin/HomeCards/form', $data);
    }

    public function actualizar(int $id): void
    {
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminHomeCards/index');
        }
        $card = $this->model->find($id);
        if (!$card) {
            redir(BASE_URL . 'AdminHomeCards/index');
        }

        $d = [
            'titulo'       => limpiar($_POST['titulo'] ?? ''),
            'descripcion'  => limpiar($_POST['descripcion'] ?? ''),
            'icono'        => limpiar($_POST['icono'] ?? ''),
            'color_fondo'  => limpiar($_POST['color_fondo'] ?? '#0d6efd'),
            'color_texto'  => limpiar($_POST['color_texto'] ?? '#ffffff'),
            'boton_texto'  => limpiar($_POST['boton_texto'] ?? 'Ver más'),
            'destino'      => trim($_POST['destino'] ?? ''),
            'orden'        => (int)($_POST['orden'] ?? 0),
            'visible'      => isset($_POST['visible']) ? 1 : 0,
            'imagen'       => $card['imagen'] ?? null,
        ];

        // Imagen opcional (reemplazo)
        if (!empty($_FILES['imagen']['name'])) {
            [$ok, $res] = uploadImage($_FILES['imagen'], 'home');
            if ($ok) {
                if (!empty($card['imagen'])) {
                    // elimina anterior
                    $old = basename($card['imagen']);
                    deleteFile($old, 'home');
                }
                $d['imagen'] = 'Assets/img/home/' . $res;
            }
        }

        $rows = $this->model->actualizar($id, $d);
        $_SESSION['flash_ok'] = $rows ? 'Tarjeta actualizada' : 'Sin cambios';
        redir(BASE_URL . 'AdminHomeCards/index');
    }

    public function borrar(int $id): void
    {
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminHomeCards/index');
        }
        $card = $this->model->find($id);
        if ($card && !empty($card['imagen'])) {
            $old = basename($card['imagen']);
            deleteFile($old, 'home');
        }
        $this->model->borrar($id);
        $_SESSION['flash_ok'] = 'Tarjeta eliminada';
        redir(BASE_URL . 'AdminHomeCards/index');
    }

    // Opcionales: visibilidad y orden rápido
    public function visible(int $id, int $v): void
    {
        $this->model->setVisible($id, $v ? 1 : 0);
        redir(BASE_URL . 'AdminHomeCards/index');
    }

    public function ordenar(int $id): void
    {
        if (!verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL . 'AdminHomeCards/index');
        }
        $orden = (int)($_POST['orden'] ?? 0);
        $this->model->setOrden($id, $orden);
        redir(BASE_URL . 'AdminHomeCards/index');
    }
    public function ordenarAjax(): void
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

        $orden = $data['orden'] ?? []; // array de IDs en el nuevo orden
        if (!is_array($orden)) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'msg' => 'Formato inválido']);
            return;
        }

        // Reasignar orden incremental desde 1
        $n = 1;
        foreach ($orden as $id) {
            $this->model->setOrden((int)$id, $n++);
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
    }
}
