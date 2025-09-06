<?php
class AdminDashboard extends Controlador
{
  private TorneosModel $torneos;
  private AdminInscripcionesModel $ins;
  public function __construct(){ parent::__construct(); requireAdmin(); $this->torneos=new TorneosModel(); $this->ins=new AdminInscripcionesModel(); }

  public function index(): void {
    $hoy = date('Y-m-d');
    $kpis = [
      'proximos'   => $this->torneos->select_one("SELECT COUNT(*) c FROM torneos WHERE estado='publicado' AND inicio>=?",[date('Y-m-d H:i:s')])['c'] ?? 0,
      'ins_hoy'    => $this->ins->select_one("SELECT COUNT(*) c FROM {$this->ins->getTable()} WHERE DATE(created_at)=?",[$hoy])['c'] ?? 0,
      'pagos_pend' => $this->ins->select_one("SELECT COUNT(*) c FROM {$this->ins->getTable()} WHERE pago_ok=0")['c'] ?? 0,
      'checkins_hoy'=> $this->ins->select_one("SELECT COUNT(*) c FROM {$this->ins->getTable()} WHERE DATE(checkin_at)=?",[$hoy])['c'] ?? 0,
    ];
    $ultimas = $this->ins->select("SELECT i.id,CONCAT(i.nombre,' ',i.apellidos) nom,i.email,i.estado,i.pago_ok,t.titulo torneo,i.created_at
                                     FROM {$this->ins->getTable()} i JOIN torneos t ON t.id=i.torneo_id
                                 ORDER BY i.created_at DESC LIMIT 8");
    $prox = $this->torneos->select("SELECT id,titulo,inicio,lugar FROM torneos WHERE estado='publicado' AND inicio>=? ORDER BY inicio ASC LIMIT 5",[date('Y-m-d H:i:s')]);
    $data=['titulo'=>'Dashboard','k'=>$kpis,'ultimas'=>$ultimas,'prox'=>$prox];
    $this->view('Admin/dashboard',$data);
  }
}
