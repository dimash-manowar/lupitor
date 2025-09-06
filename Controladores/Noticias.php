<?php
class Noticias extends Controlador
{

    private NoticiaMediaModel $linkModel;    // puente noticia <-> medios

    public function __construct()
    {
        parent::__construct();
        $this->model     = new NoticiasModel();
        $this->linkModel = new NoticiaMediaModel();
    }

    // GET /Noticias[/<page>]?cat=club|ajedrez|escuela&q=...&orden=...
    public function index(int $pagina = 1): void
    {
        $categoria = $_GET['categoria'] ?? null;
        $categoria = in_array($categoria, ['club', 'ajedrez', 'escuela'], true) ? $categoria : null;

        $q     = trim((string)($_GET['q'] ?? ''));
        $orden = $_GET['orden'] ?? 'recientes';
        $per   = (int)($_GET['per'] ?? 9);
        $per   = in_array($per, [6, 9, 12, 18, 24], true) ? $per : 9;

        $res = $this->model->listarPublico($categoria, max(1, $pagina), $per, $q, $orden);

        $data = [
            'titulo'    => 'Noticias',
            'csrf'      => csrfToken(),
            'items'     => $res['items'] ?? [],
            'meta'      => [
                'page'        => $res['page'] ?? 1,
                'per'         => $res['per'] ?? $per,
                'total'       => $res['total'] ?? 0,
                'total_pages' => $res['total_pages'] ?? 1,
            ],
            'categoria' => $categoria ?? '',
            'q'         => $q,
            'orden'     => $orden,
            'base_url'  => BASE_URL . 'Noticias/index/',
            'hideNavbar' => true,
        ];

        // AJAX parcial
        $esAjax = (isset($_GET['ajax']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');
        if ($esAjax) {
            $newsItems = $data['items'];
            ob_start();
            require BASE_PATH . 'Views/Noticias/_cards.php';
            $htmlCards = ob_get_clean();           

            ob_start();
            require BASE_PATH . 'Views/Noticias/_paginacion.php';
            $htmlPag = ob_get_clean();

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'         => true,
                'cards'      => $htmlCards,
                'pagination' => $htmlPag,
                'newsItems'  => $newsItems,
                'total'      => $data['meta']['total'],
                'page'       => $data['meta']['page'],
                'per'        => $data['meta']['per'],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }

        $this->view('Noticias/index', $data);
    }


    // GET /Noticias/ver/<slug>
    public function ver(string $slug): void
    {
        $n = $this->model->buscarPorSlug($slug);
        if (!$n) {
            http_response_code(404);
            $this->view('Errors/404', ['titulo' => 'Noticia no encontrada']);
            return;
        }
        // Galería asociada
        $galeria = $this->linkModel->listarPorNoticia((int)$n['id']);

        $data = [
            'titulo'  => $n['titulo'],
            'csrf'    => csrfToken(),
            'n'       => $n,
            'galeria' => $galeria,
            'hideNavbar' => true, // 👈 no mostrar navbar
            'hideHero'   => true, // 👈 por si queda algún resto de hero
        ];
        $this->view('Noticias/ver', $data);
    }
}
