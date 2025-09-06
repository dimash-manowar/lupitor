<?php
class AdminNav extends Controlador
{
    

    public function __construct()
    {
        parent::__construct();
        $this->model = new NavItemModel();
    }

    public function index(): void
    {
        requireLogin();
        requireAdmin();

        $items = $this->model->listarPrincipal();

        $data = [
            'titulo' => 'Gestión del Menú',
            'items'  => $items,
        ];

        $this->view('Admin/nav_index', $data);
    }
}
