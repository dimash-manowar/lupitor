<?php
class Home extends Controlador
{

    private GaleriaModel $gal;
    public function __construct()
    {
        parent::__construct();
        $this->gal = new GaleriaModel();
    }

    // GET /Home/index[/<page>]?nivel=...&q=...&orden=...&per=... [&ajax=1]
    public function index(int $pagina = 1): void
    {

        $tesModel  = new HomeTestimonioModel();
        $ejercicioModel = new EjercicioModel();
        $eventosModel = new EventoModel();

        // --- leer filtros ---
        $nivel = $_GET['nivel'] ?? '';
        $nivel = in_array($nivel, ['Iniciación', 'Intermedio', 'Avanzado'], true) ? $nivel : null;

        $q = trim((string)($_GET['q'] ?? ''));
        if (mb_strlen($q) > 100) $q = mb_substr($q, 0, 100);

        $orden = $_GET['orden'] ?? 'recientes';
        $permitOrden = ['recientes', 'antiguos', 'nivel_asc', 'nivel_desc', 'titulo_asc', 'titulo_desc'];
        if (!in_array($orden, $permitOrden, true)) $orden = 'recientes';

        $per = (int)($_GET['per'] ?? 9);
        $permitPer = [9, 12, 18, 24];
        if (!in_array($per, $permitPer, true)) $per = 9;

        // --- datos ejercicios ---
        $res = $ejercicioModel->listarPublico($nivel, max(1, $pagina), $per, $q, $orden);
        $navModel = new NavItemModel();
        $menuItems = $navModel->listarPrincipal();
        $cardModel = new HomeCardModel();
        $torModel = new TorneosModel();
        $prox = $torModel->proximosPublico(1);
        $destacado = $prox[0] ?? null;
        $galeria = $this->gal->ultimosPublicos(12);
        $galStats = $this->gal->contarVisibles();
        $carouselAlumnos = $this->gal->fotosParaCarousel(8, 'alumnos-del-club'); // 8 ítems
        $data['carouselAlumnos'] = $carouselAlumnos;

        if ($destacado && !empty($destacado['cupo'])) {
            $insModel = new TorneoInscripcionModel();
            $destacado['inscritos'] = $insModel->contarPorTorneo((int)$destacado['id']);
        }
        $data = [
            'titulo'     => 'Inicio',
            'csrf'       => csrfToken(),
            'destacado'  => $destacado,
            'ejercicios' => $res['items'] ?? [],
            'testimonios' => $tesModel->listarVisibles(),
            'galeria'    => $galeria,
            'galStats'   => $galStats,
            'carouselAlumnos' => $carouselAlumnos,
            'meta'       => [
                'page'        => $res['page'] ?? 1,
                'per'         => $res['per'] ?? $per,
                'total_pages' => $res['total_pages'] ?? 1,
                'total'       => $res['total'] ?? 0,
            ],
            'nivel'       => $nivel ?? '',
            'q'           => $q,
            'orden'       => $orden,

            'home_cards'  => homeCardsHtml(),
            'menu_items'  => $menuItems,
            'home_cards'  => $cardModel->listarVisibles(3),
            'base_url'    => BASE_URL . 'Home/index/',
        ];

        // --- detectar si es AJAX (solo cuando realmente hay ?ajax=1 Y header correcto) ---
        $esAjax = (
            isset($_GET['ajax'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch'
        );

        if ($esAjax) {
            // --- devolver JSON con parciales ---
            $ejercicios = $data['ejercicios']; // importante: pasar a la vista
            ob_start();
            require BASE_PATH . 'Views/Home/_cards.php';
            $htmlCards = ob_get_clean();

            ob_start();
            require BASE_PATH . 'Views/Home/_paginacion.php';
            $htmlPag = ob_get_clean();



            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'ok'         => true,
                'cards'      => $htmlCards,
                'pagination' => $htmlPag,
                'total'      => $data['meta']['total'],
                'page'       => $data['meta']['page'],
                'per'        => $data['meta']['per'],
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        $noticiasModel = new NoticiasModel();
        $ultimas = $noticiasModel->listarPublico(null, 1, 3, null, 'recientes');
        $data['ultimas_noticias'] = $ultimas['items'] ?? [];
        // --- HTML completo (Home con todo) ---
        $this->view('Home/index', $data);
    }
}
