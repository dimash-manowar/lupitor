<?php
class Eventos extends Controlador
{
    private EventoModel $eventoModel;

    public function __construct()
    {
        parent::__construct();
        $this->eventoModel = new EventoModel(); // usa tu nombre real de modelo
    }

    // GET /Eventos[/<page>]?modalidad=...&q=...&desde=YYYY-MM-DD&hasta=YYYY-MM-DD&orden=...
    public function index(int $pagina = 1): void
    {
        $modalidad = trim((string)($_GET['modalidad'] ?? ''));
        $q         = trim((string)($_GET['q'] ?? ''));
        $desde     = trim((string)($_GET['desde'] ?? ''));
        $hasta     = trim((string)($_GET['hasta'] ?? ''));
        $orden     = $_GET['orden'] ?? 'proximos'; // proximos(fecha asc), fecha_desc, titulo_asc, titulo_desc, recientes(created_at desc)
        $per       = (int)($_GET['per'] ?? 9);
        $per       = in_array($per, [6,9,12,18,24], true) ? $per : 9;

        // ==> Ajusta al nombre real del método de tu modelo:
        // Espera que filtre solo "publicados" y con fecha futura si $orden = 'proximos'
        $res = $this->eventoModel->listarPublico($modalidad ?: null, max(1,$pagina), $per, $q, $orden, $desde ?: null, $hasta ?: null);

        $data = [
            'titulo'    => 'Eventos',
            'csrf'      => csrfToken(),
            'items'     => $res['items'] ?? [],
            'meta'      => [
                'page'        => $res['page'] ?? 1,
                'per'         => $res['per'] ?? $per,
                'total'       => $res['total'] ?? 0,
                'total_pages' => $res['total_pages'] ?? 1,
            ],
            'modalidad' => $modalidad,
            'q'         => $q,
            'desde'     => $desde,
            'hasta'     => $hasta,
            'orden'     => $orden,
            'base_url'  => BASE_URL.'Eventos/index/',
            'hideNavbar'=> true, // como Noticias: sin navbar visible pero con estilos
        ];

        // AJAX parcial
        $esAjax = (isset($_GET['ajax']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch');
        if ($esAjax) {
            $evtItems = $data['items'];
            ob_start(); require BASE_PATH . 'Views/Eventos/_cards.php'; $htmlCards = ob_get_clean();
            ob_start(); require BASE_PATH . 'Views/Eventos/_paginacion.php'; $htmlPag = ob_get_clean();

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

        $this->view('Eventos/index', $data);
    }
}
