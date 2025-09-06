<?php
class Agenda extends Controlador
{
    public function __construct()
    {
        parent::__construct();
        
    }
    public function index(int $page = 1): void
    {
        $per = 12;
        $mdl = new EventoModel();
        $data = $this->model->listar($page, $per);
        $data['titulo'] = 'Agenda';
        $this->view('Agenda/index', $data);
    }
}
