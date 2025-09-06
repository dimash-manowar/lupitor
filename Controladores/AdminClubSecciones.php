<?php
class AdminClubSecciones extends Controlador
{
    
    public function __construct(){ parent::__construct(); requireAdmin(); $this->model=new ClubSeccionModel(); }

    public function index(int $pagina=1): void {
        $f = [
            'estado' => $_GET['estado'] ?? '',
            'tipo'   => $_GET['tipo'] ?? '',
            'q'      => trim((string)($_GET['q'] ?? '')),
            'orden'  => $_GET['orden'] ?? 'orden',
        ];
        $res=$this->model->listar($f, max(1,$pagina), 12);
        $data=array_merge($res, ['titulo'=>'Secciones del club','csrf'=>csrfToken(),'f'=>$f]);
        $this->view('Admin/club-sec-index',$data);
    }

    public function crear(): void {
        $data=['titulo'=>'Nueva sección','csrf'=>csrfToken()];
        $this->view('Admin/club-sec-form',$data);
    }

    public function guardar(): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminClubSecciones/index');
        }
        $titulo = trim($_POST['titulo'] ?? '');
        $slug   = slugify($titulo);
        $i=2;$base=$slug; while(!$this->model->slugDisponible($slug)) $slug=$base.'-'.$i++;

        $d = [
            'tipo'        => in_array($_POST['tipo'] ?? 'info',['historia','info','otra'],true) ? $_POST['tipo']:'info',
            'titulo'      => $titulo,
            'slug'        => $slug,
            'resumen'     => trim((string)($_POST['resumen'] ?? '')),
            'cuerpo_html' => (string)($_POST['cuerpo_html'] ?? ''),
            'estado'      => in_array($_POST['estado'] ?? 'publicado',['borrador','publicado'],true) ? $_POST['estado']:'publicado',
            'orden'       => (int)($_POST['orden'] ?? 0),
        ];

        if (!empty($_FILES['portada']['name'])) {
            [$ok,$res] = uploadImage($_FILES['portada'],'club', ['jpg','jpeg','png','webp'], 4);
            if ($ok) $d['portada'] = 'Assets/img/club/'.$res;
        }

        $id=$this->model->crear($d);
        $_SESSION['flash_ok']=$id?'Sección creada':'No se pudo crear';
        redir(BASE_URL.'AdminClubSecciones/index');
    }

    public function editar(int $id): void {
        $sec=$this->model->buscarPorId($id);
        if(!$sec){ $_SESSION['flash_error']='Sección no encontrada'; redir(BASE_URL.'AdminClubSecciones/index'); }
        $data=['titulo'=>'Editar sección','csrf'=>csrfToken(),'sec'=>$sec];
        $this->view('Admin/club-sec-form',$data);
    }

    public function actualizar(int $id): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminClubSecciones/index');
        }
        $sec=$this->model->buscarPorId($id); if(!$sec) redir(BASE_URL.'AdminClubSecciones/index');

        $titulo = trim($_POST['titulo'] ?? '');
        $slug   = slugify($titulo);
        $i=2;$base=$slug; while(!$this->model->slugDisponible($slug,$id)) $slug=$base.'-'.$i++;

        $d=[
            'tipo'        => in_array($_POST['tipo'] ?? 'info',['historia','info','otra'],true) ? $_POST['tipo']:'info',
            'titulo'      => $titulo,
            'slug'        => $slug,
            'resumen'     => trim((string)($_POST['resumen'] ?? '')),
            'cuerpo_html' => (string)($_POST['cuerpo_html'] ?? ''),
            'estado'      => in_array($_POST['estado'] ?? 'publicado',['borrador','publicado'],true) ? $_POST['estado']:'publicado',
            'orden'       => (int)($_POST['orden'] ?? 0),
            'portada'     => $sec['portada'] ?? null
        ];
        if (!empty($_FILES['portada']['name'])) {
            [$ok,$res]=uploadImage($_FILES['portada'],'club',['jpg','jpeg','png','webp'],4);
            if ($ok) {
                if (!empty($d['portada'])) deleteFile(basename($d['portada']),'club');
                $d['portada']='Assets/img/club/'.$res;
            }
        }
        $this->model->actualizar($id,$d);
        $_SESSION['flash_ok']='Sección actualizada';
        redir(BASE_URL.'AdminClubSecciones/editar/'.$id);
    }

    public function eliminar(int $id): void {
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminClubSecciones/index');
        }
        $sec=$this->model->buscarPorId($id);
        if($sec && !empty($sec['portada'])) deleteFile(basename($sec['portada']),'club');
        $this->model->eliminar($id);
        $_SESSION['flash_ok']='Sección eliminada';
        redir(BASE_URL.'AdminClubSecciones/index');
    }
}
