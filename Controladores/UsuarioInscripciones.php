<?php
class UsuarioInscripciones extends Controlador
{
    private TorneoInscripcionModel $ins;

    public function __construct(){
        parent::__construct();  requireLogin();
        $this->ins = new TorneoInscripcionModel();
    }

    public function index(int $pagina=1): void {
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $r = $this->ins->listarPorUsuario($uid, max(1,$pagina), 15);
        $data = array_merge($r, ['titulo'=>'Mis inscripciones', 'csrf'=>csrfToken()]);
        $this->view('Usuario/inscripciones-index', $data);
    }

    public function pdf(int $id): void {
        
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $r = $this->ins->findByIdAndUser($id, $uid);
        if (!$r) { $_SESSION['flash_error']='No encontrado'; redir(BASE_URL.'UsuarioInscripciones/index'); }

        // renderizamos una vista a HTML y la enviamos a Dompdf
        ob_start();
        $data = ['r'=>$r];
        extract($data, EXTR_OVERWRITE);
        require BASE_PATH . 'Views/Admin/Pdf/recibo-inscripcion.php'; // usa la misma plantilla que ya tienes
        $html = ob_get_clean();

        require_once BASE_PATH.'Core/Pdf.php';
        Pdf::stream($html, 'recibo-insc-'.$id.'.pdf', 'A4', 'portrait', true);
    }
}
