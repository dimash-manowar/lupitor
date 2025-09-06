<?php
class Ranking extends Controlador
{
     public function __construct()
    {
        parent::__construct();
        
    }
    public function index(?string $temporada = null, int $page = 1): void
    {
        $temporada = $temporada ?: date('Y');
        $per = 25;
        $mdl = new RankingModel();
        $data = $mdl->tabla($temporada, $page, $per);
        $data['temporada'] = $temporada;
        $data['titulo'] = 'Ranking ' . $temporada;
        $this->view('Ranking/index', $data);
    }
}
