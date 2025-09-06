<?php
class Club extends Controlador
{
    private ClubSeccionModel $secModel;
    private ClubPersonaModel $perModel;

    public function __construct()
    {
        parent::__construct();
        $this->secModel = new ClubSeccionModel();
        $this->perModel = new ClubPersonaModel();
    }

    // Listado de secciones publicadas + accesos rápidos a Socios/Alumnos
    public function index(): void
    {
        $secs = $this->secModel->listarPublico(null);
        $data = ['titulo' => 'Sobre el club', 'hideNavbar' => true, 'secciones' => $secs];
        $this->view('Club/index', $data);
    }

    // Ver una sección por slug
    public function ver(string $slug): void
    {
        $sec = $this->secModel->buscarPorSlug($slug);
        if (!$sec || $sec['estado'] !== 'publicado') {
            header('Location: ' . BASE_URL . 'Club');
            return;
        }
        $data = ['titulo' => $sec['titulo'], 'hideNavbar' => true, 'sec' => $sec];
        $this->view('Club/ver', $data);
    }

    // (Opcional) Listados específicos
    public function socios(int $pagina = 1): void
    {
        $q     = trim((string)($_GET['q'] ?? ''));
        $orden = $_GET['orden'] ?? 'orden'; // orden | nombre_asc | nombre_desc | recientes
        $per   = (int)($_GET['per'] ?? 12);
        $per   = in_array($per, [6, 9, 12, 18, 24], true) ? $per : 12;

        $r = $this->perModel->listar([
            'tipo'    => 'socio',
            'visible' => 1,
            'q'       => $q,
            'orden'   => $orden,
        ], max(1, $pagina), $per);

        $data = [
            'titulo'     => 'Socios',
            'hideNavbar' => true,
            'items'      => $r['items'],
            'meta'       => [
                'page' => $r['page'],
                'per' => $r['per'],
                'total' => $r['total'],
                'total_pages' => $r['total_pages']
            ],
            'q'          => $q,
            'orden'      => $orden,
            'per'        => $per,
            'base_url'   => BASE_URL . 'Club/socios/',
        ];

        $esAjax = (isset($_GET['ajax']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');
        if ($esAjax) {
            $peopleItems = $data['items'];
            ob_start();
            require BASE_PATH . 'Views/Club/_personas_cards.php';
            $htmlCards = ob_get_clean();
            ob_start();
            require BASE_PATH . 'Views/Club/_personas_paginacion.php';
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

        $this->view('Club/personas', $data);
    }

    public function alumnos(int $pagina = 1): void
    {
        $q     = trim((string)($_GET['q'] ?? ''));
        $orden = $_GET['orden'] ?? 'orden';
        $per   = (int)($_GET['per'] ?? 12);
        $per   = in_array($per, [6, 9, 12, 18, 24], true) ? $per : 12;

        $r = $this->perModel->listar([
            'tipo'    => 'alumno',
            'visible' => 1,
            'q'       => $q,
            'orden'   => $orden,
        ], max(1, $pagina), $per);

        $data = [
            'titulo'     => 'Alumnos',
            'hideNavbar' => true,
            'items'      => $r['items'],
            'meta'       => [
                'page' => $r['page'],
                'per' => $r['per'],
                'total' => $r['total'],
                'total_pages' => $r['total_pages']
            ],
            'q'          => $q,
            'orden'      => $orden,
            'per'        => $per,
            'base_url'   => BASE_URL . 'Club/alumnos/',
        ];

        $esAjax = (isset($_GET['ajax']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');
        if ($esAjax) {
            $peopleItems = $data['items'];
            ob_start();
            require BASE_PATH . 'Views/Club/_personas_cards.php';
            $htmlCards = ob_get_clean();
            ob_start();
            require BASE_PATH . 'Views/Club/_personas_paginacion.php';
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

        $this->view('Club/personas', $data);
    }
}
