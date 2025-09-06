<?php
class AdminCheckin extends Controlador
{
  
  public function __construct(){ parent::__construct(); requireAdmin(); $this->model=new AdminInscripcionesModel(); }

  public function panel(int $torneoId): void {
    $t = (new TorneosModel())->buscarPorId($torneoId);
    if (!$t) { $_SESSION['flash_error']='Torneo no encontrado'; redir(BASE_URL.'AdminTorneos/index'); }
    $this->view('Admin/checkin-panel', ['t'=>$t,'csrf'=>csrfToken()]);
  }

  public function confirmar(): void {
    if ($_SERVER['REQUEST_METHOD']!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>'CSRF']); return; }
    $token = $_POST['token'] ?? '';
    $torneoId = (int)($_POST['torneo_id'] ?? 0);
    $row = $this->model->select_one("SELECT id, torneo_id, nombre, apellidos, checkin_at FROM {$this->model->getTable()} WHERE checkin_token=? LIMIT 1", [$token]);
    if (!$row || (int)$row['torneo_id'] !== $torneoId) { echo json_encode(['ok'=>false,'msg'=>'No encontrado']); return; }
    if (!empty($row['checkin_at'])) { echo json_encode(['ok'=>true,'msg'=>'Ya estaba check-in','repeat'=>1]); return; }
    $n = $this->model->update("UPDATE {$this->model->getTable()} SET checkin_at=NOW(), estado=IF(estado='anulada','anulada','confirmada') WHERE id=?", [$row['id']]);
    echo json_encode(['ok'=>$n>0,'msg'=>'Check-in OK','nombre'=>$row['nombre'].' '.$row['apellidos']]);
  }
}
