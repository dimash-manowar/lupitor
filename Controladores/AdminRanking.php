<?php
class AdminRanking extends Controlador
{
    public function __construct()
    {
        parent::__construct();
        
    }
    public function index(): void
    {
       requireAdmin();
        $temporada = $_GET['temporada'] ?? date('Y');
        $this->view('Admin/Ranking/index', [
            'csrf' => csrfToken(),
            'temporada' => $temporada,
            'titulo' => 'Ranking — Importar'
        ]);
    }


    public function importar(): void
    {
        requireRole('admin');
        if (!verificarCsrf($_POST['csrf'] ?? '')) exit('CSRF');
        $temporada = $_POST['temporada'] ?? date('Y');
        if (!empty($_FILES['csv']['tmp_name'])) {
            $fh = fopen($_FILES['csv']['tmp_name'], 'r');
            $mdl = new RankingModel();
            while (($r = fgetcsv($fh, 0, ';')) !== false) {
                if (count($r) < 5) continue; // pos;jugador;elo;puntos;club
                $mdl->upsert([
                    'temporada' => $temporada,
                    'posicion' => (int)$r[0],
                    'jugador' => trim($r[1]),
                    'elo' => (int)$r[2],
                    'puntos' => (float)$r[3],
                    'club' => trim($r[4]),
                ]);
            }
        }
        header('Location: ' . BASE_URL . 'AdminRanking/index?temporada=' . urlencode($temporada) . '&s=imported');
    }


    public function vaciarTemporada(): void
    {
        requireRole('admin');
        if (!verificarCsrf($_POST['csrf'] ?? '')) exit('CSRF');
        $temporada = $_POST['temporada'] ?? date('Y');
        $mdl = new RankingModel();
        $mdl->clearSeason($temporada);
        header('Location: ' . BASE_URL . 'AdminRanking/index?temporada=' . urlencode($temporada) . '&s=cleared');
    }
}
