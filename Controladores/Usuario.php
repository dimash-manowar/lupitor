<?php
class Usuario extends Controlador
{
  private TorneoInscripcionModel $ins;
 

  public function __construct()
  {
    parent::__construct();
    requireLogin();
    $this->ins = new TorneoInscripcionModel();
    
  }

  public function index(): void
  {
    $uid = (int)($_SESSION['user']['id'] ?? 0);

    // Menú lateral
    $nav = new NavItemModel();
    $userItems = $nav->menuTree('usuario');   

    // Próximo torneo del usuario y última inscripción
    $proximo = $this->ins->proximoPorUsuario($uid);
    $ultima  = $this->ins->ultimaPorUsuario($uid);

    $data = [
      'titulo'        => 'Panel',
      'userItems'     => $userItems,            
      'proximo'       => $proximo,
      'ultima'        => $ultima
    ];
    $this->view('Usuario/index', $data);
  }
}
