<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<h1 class="h4 text-light mb-3">Dashboard</h1>
<div class="row g-3">
  <?php
    $cards = [
      ['Proximos torneos',$k['proximos']??0,'bi bi-calendar-event','bg-primary'],
      ['Inscripciones hoy',$k['ins_hoy']??0,'bi bi-person-plus','bg-success'],
      ['Pagos pendientes',$k['pagos_pend']??0,'bi bi-cash-coin','bg-warning text-dark'],
      ['Check-ins hoy',$k['checkins_hoy']??0,'bi bi-qr-code','bg-info text-dark']
    ];
  ?>
  <?php foreach($cards as [$t,$n,$icon,$cls]): ?>
  <div class="col-6 col-md-3">
    <div class="card text-light <?= $cls ?>">
      <div class="card-body d-flex align-items-center justify-content-between">
        <div>
          <div class="small"><?= $t ?></div>
          <div class="fs-3 fw-bold"><?= (int)$n ?></div>
        </div>
        <i class="<?= $icon ?> fs-1 opacity-75"></i>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-6">
    <div class="card bg-dark text-light h-100">
      <div class="card-header border-secondary">Últimas inscripciones</div>
      <div class="card-body">
        <div class="table-responsive">
        <table class="table table-dark table-sm align-middle">
          <thead><tr><th>ID</th><th>Jugador</th><th>Torneo</th><th>Pago</th><th>Estado</th><th>Alta</th></tr></thead>
          <tbody>
            <?php foreach (($ultimas??[]) as $r): ?>
              <tr>
                <td>#<?= (int)$r['id'] ?></td>
                <td><?= htmlspecialchars($r['nom'] ?? '') ?><br><span class="text-secondary small"><?= htmlspecialchars($r['email'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($r['torneo'] ?? '') ?></td>
                <td><?= ((int)($r['pago_ok']??0) ? 'OK':'Pend.') ?></td>
                <td><?= htmlspecialchars($r['estado'] ?? '') ?></td>
                <td><?= date('d/m H:i', strtotime($r['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card bg-dark text-light h-100">
      <div class="card-header border-secondary">Próximos torneos</div>
      <div class="card-body">
        <ul class="list-group list-group-flush">
          <?php foreach (($prox??[]) as $t): ?>
            <li class="list-group-item bg-dark text-light d-flex justify-content-between">
              <span><?= htmlspecialchars($t['titulo']) ?> <span class="text-secondary">· <?= htmlspecialchars($t['lugar'] ?? '') ?></span></span>
              <span><?= date('d/m H:i', strtotime($t['inicio'])) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>
