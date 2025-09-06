<?php
class AdminEventos extends Controlador
{
    
    public function __construct()
    {
        parent::__construct();
        
        requireAdmin();
        
    }
    public function index(int $page = 1): void
    {
        
        
        $data = $this->model->listar($page, 20);
        $data['csrf'] = csrfToken();
        $data['titulo'] = 'Eventos';
        $this->view('Admin/Eventos/lista', $data);
    }


    public function crear(): void
    {
        
        $this->view('Admin/Eventos/form', ['csrf' => csrfToken(), 'e' => null, 'titulo' => 'Nuevo evento']);
    }


    public function guardar(): void
    {
        
        if (!verificarCsrf($_POST['csrf'] ?? '')) exit('CSRF');
        $mdl = new EventoModel();
        $ok = $this->model->crear($_POST + ['creado_por' => $_SESSION['user']['id'] ?? null]);
        header('Location: ' . BASE_URL . 'AdminEventos/index' . ($ok ? '?s=created' : '?e=fail'));
    }


    public function editar(int $id): void
    {
        
        
        $e = $this->model->buscar($id);
        if (!$e) exit('No existe');
        $this->view('Admin/Eventos/form', ['csrf' => csrfToken(), 'e' => $e, 'titulo' => 'Editar evento']);
    }


    public function actualizar(int $id): void
    {
        
        if (!verificarCsrf($_POST['csrf'] ?? '')) exit('CSRF');
        
        $ok = $this->model->actualizar($id, $_POST);
        header('Location: ' . BASE_URL . 'AdminEventos/index' . ($ok ? '?s=updated' : '?e=fail'));
    }


    public function borrar(int $id): void
    {
        
        if (!verificarCsrf($_POST['csrf'] ?? '')) exit('CSRF');
        
        $ok = $this->model->eliminar($id);
        header('Location: ' . BASE_URL . 'AdminEventos/index' . ($ok ? '?s=deleted' : '?e=fail'));
    }
}
