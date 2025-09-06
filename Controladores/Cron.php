<?php
class Cron extends Controlador
{
  private AdminInscripcionesModel $ins;
  public function __construct(){ parent::__construct(); $this->ins = new AdminInscripcionesModel(); }

  public function recordatorios(): void {
    $key = $_GET['key'] ?? '';
    if ($key !== 'TU_SECRET') { http_response_code(403); exit('Forbidden'); }

    date_default_timezone_set('Europe/Madrid');
    $hoy = date('Y-m-d');

    $torneos = $this->ins->torneosHoy($hoy);

    foreach ($torneos as $t) {
      $insc = $this->ins->inscripcionesParaRecordatorio((int)$t['id']);

      foreach ($insc as $r) {
        $qrUrl   = BASE_URL.'Inscripcion/qr?token='.$r['checkin_token'];
        $subject = 'Recordatorio — '.$t['titulo'];
        $hora    = date('H:i', strtotime($t['inicio']));
        $lugar   = $t['lugar'] ?? '';
        $html = '<p>Hola <strong>'.htmlspecialchars($r['nombre'])."</strong>, te recordamos que <strong>hoy</strong> es el torneo <strong>".htmlspecialchars($t['titulo'])."</strong>.</p>"
              . '<p>Hora: '.$hora.' · Lugar: '.htmlspecialchars($lugar).'</p>'
              . '<p>Presenta este QR en acceso:</p><p><img src="'.$qrUrl.'" width="160" height="160" style="border:1px solid #eee;border-radius:8px"></p>';

        enviarCorreo($r['email'], $r['nombre'], $subject, $html);
      }

      $this->ins->marcarRecordatoriosEnviados((int)$t['id']);
    }
    echo 'OK';
  }
}
