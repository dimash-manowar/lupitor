<?php
class Inscripcion extends Controlador
{
  
  public function __construct(){ parent::__construct(); $this->model = new AdminInscripcionesModel(); }

  // Imagen QR (proxy simple a Google Chart)
  public function qr(): void {
    $token = $_GET['token'] ?? '';
    if (!preg_match('~^[a-f0-9]{32}$~', $token)) { http_response_code(400); exit; }
    $data = BASE_URL.'Inscripcion/presentar?token='.$token;
    $qr = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.urlencode($data).'&choe=UTF-8';
    header('Content-Type: image/png');
    echo @file_get_contents($qr) ?: '';
  }

  // Página que muestra tu QR
  public function presentar(): void {
    $token = $_GET['token'] ?? '';
    $row = $this->model->select_one(
      "SELECT i.*, t.titulo AS torneo, t.inicio, t.lugar
         FROM {$this->model->getTable()} i
         JOIN torneos t ON t.id=i.torneo_id
        WHERE i.checkin_token=? LIMIT 1", [$token]
    );
    if (!$row) { http_response_code(404); exit('No encontrado'); }
    $this->view('Public/mi-qr', ['r'=>$row,'token'=>$token]);
  }
}
