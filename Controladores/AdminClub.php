<?php
class AdminClub extends Controlador
{
    
    public function __construct(){ parent::__construct(); requireAdmin(); $this->model = new ClubModel(); }

    public function index(): void
    {
        $data = ['titulo'=>'Club','csrf'=>csrfToken(),'c'=>$this->model->get()];
        $this->view('Admin/club-form', $data);
    }

    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminClub/index');
        }

        $c = [
            'titulo'      => trim($_POST['titulo'] ?? ''),
            'subtitulo'   => trim($_POST['subtitulo'] ?? ''),
            'cuerpo_html' => (string)($_POST['cuerpo_html'] ?? ''),
            'direccion'   => trim($_POST['direccion'] ?? ''),
            'email'       => trim($_POST['email'] ?? ''),
            'telefono'    => trim($_POST['telefono'] ?? ''),
            'mapa_iframe' => trim($_POST['mapa_iframe'] ?? ''),
        ];

        // horarios
        $horarios = [];
        foreach (['lun','mar','mie','jue','vie','sab','dom'] as $d) {
            if (isset($_POST["h_$d"])) $horarios[$d] = trim($_POST["h_$d"]);
        }
        if ($horarios) $c['horarios'] = $horarios;

        // portada (opcional)
        if (!empty($_FILES['portada']['name'])) {
            [$ok, $res] = uploadImage($_FILES['portada'], 'club', ['jpg','jpeg','png','webp'], 4);
            if ($ok) $c['portada'] = 'Assets/img/club/' . $res;
        }

        $this->model->actualizarInfo($c);
        $_SESSION['flash_ok'] = 'Datos del club guardados';
        redir(BASE_URL.'AdminClub/index');
    }
}
