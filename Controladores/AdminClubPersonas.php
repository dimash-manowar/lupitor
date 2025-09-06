<?php
class AdminClubPersonas extends Controlador
{
    
    public function __construct(){ parent::__construct(); requireAdmin(); $this->model=new ClubPersonaModel(); }

    public function index(int $pagina=1): void {
        $f=[
            'tipo'    => $_GET['tipo'] ?? '',
            'visible' => $_GET['visible'] ?? '',
            'q'       => trim((string)($_GET['q'] ?? '')),
            'orden'   => $_GET['orden'] ?? 'orden'
        ];
        $res=$this->model->listar($f, max(1,$pagina), 12);
        $data=array_merge($res,['titulo'=>'Personas (socios/alumnos)','csrf'=>csrfToken(),'f'=>$f]);
        $this->view('Admin/club-pers-index',$data);
    }

    public function crear(): void {
        $data=['titulo'=>'Nueva persona','csrf'=>csrfToken()];
        $this->view('Admin/club-pers-form',$data);
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminClubPersonas/index');
        }
        $d = [
            'tipo'      => in_array($_POST['tipo'] ?? 'socio',['socio','alumno','entrenador'],true) ? $_POST['tipo']:'socio',
            'nombre'    => trim($_POST['nombre'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'bio'       => (string)($_POST['bio'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'elo'       => $_POST['elo'] !== '' ? (int)$_POST['elo'] : null,
            'visible'   => isset($_POST['visible']) ? 1 : 0,
            'orden'     => (int)($_POST['orden'] ?? 0),
        ];
        if (!empty($_FILES['foto']['name'])) {
            [$ok,$res]=uploadImage($_FILES['foto'],'club',['jpg','jpeg','png','webp'],4);
            if ($ok) $d['foto']='Assets/img/club/'.$res;
        }
        $this->model->crear($d);
        $_SESSION['flash_ok']='Persona creada';
        redir(BASE_URL.'AdminClubPersonas/index');
    }

    public function editar(int $id): void {
        $p=$this->model->obtener($id);
        if(!$p){ $_SESSION['flash_error']='No encontrado'; redir(BASE_URL.'AdminClubPersonas/index'); }
        $data=['titulo'=>'Editar persona','csrf'=>csrfToken(),'p'=>$p];
        $this->view('Admin/club-pers-form',$data);
    }

    public function actualizar(int $id): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminClubPersonas/index');
        }
        $p=$this->model->obtener($id); if(!$p) redir(BASE_URL.'AdminClubPersonas/index');

        $d=[
            'tipo'      => in_array($_POST['tipo'] ?? 'socio',['socio','alumno','entrenador'],true) ? $_POST['tipo']:'socio',
            'nombre'    => trim($_POST['nombre'] ?? ''),
            'apellidos' => trim($_POST['apellidos'] ?? ''),
            'bio'       => (string)($_POST['bio'] ?? ''),
            'email'     => trim($_POST['email'] ?? ''),
            'elo'       => $_POST['elo'] !== '' ? (int)$_POST['elo'] : null,
            'visible'   => isset($_POST['visible']) ? 1 : 0,
            'orden'     => (int)($_POST['orden'] ?? 0),
            'foto'      => $p['foto'] ?? null
        ];
        if (!empty($_FILES['foto']['name'])) {
            [$ok,$res]=uploadImage($_FILES['foto'],'club',['jpg','jpeg','png','webp'],4);
            if ($ok) {
                if (!empty($d['foto'])) deleteFile(basename($d['foto']),'club');
                $d['foto']='Assets/img/club/'.$res;
            }
        }
        $this->model->actualizar($id,$d);
        $_SESSION['flash_ok']='Persona actualizada';
        redir(BASE_URL.'AdminClubPersonas/editar/'.$id);
    }

    public function eliminar(int $id): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminClubPersonas/index');
        }
        $p=$this->model->obtener($id);
        if($p && !empty($p['foto'])) deleteFile(basename($p['foto']),'club');
        $this->model->eliminar($id);
        $_SESSION['flash_ok']='Eliminado';
        redir(BASE_URL.'AdminClubPersonas/index');
    }
}
