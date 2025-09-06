<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <div>
      <h1 class="h4 m-0">Inscripciones — <?= htmlspecialchars($t['titulo'] ?? '') ?></h1>
      <small class="text-secondary"><?= date('d/m/Y H:i', strtotime($t['inicio'])) ?> · <?= htmlspecialchars($t['lugar'] ?? '') ?></small>
    </div>
    <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>AdminTorneos/index">Volver</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-sm-4"><input name="q" class="form-control" placeholder="Buscar…" value="<?= htmlspecialchars($q ?? '') ?>"></div>
    <div class="col-sm-2 d-grid"><button class="btn btn-outline-light">Filtrar</button></div>
  </form>

  <?php $items=$items??[]; ?>
  <div class="table-responsive">
    <table class="table table-dark table-striped align-middle">
      <thead><tr><th>Fecha</th><th>Nombre</th><th>Email</th><th>ELO</th><th>Pago</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach($items as $i): ?>
        <tr>
          <td><small class="text-secondary"><?= htmlspecialchars($i['created_at']) ?></small></td>
          <td><?= htmlspecialchars(trim(($i['nombre']??'').' '.($i['apellidos']??''))) ?></td>
          <td><?= htmlspecialchars($i['email']) ?></td>
          <td><?= (int)($i['elo'] ?? 0) ?></td>
          <td><?= htmlspecialchars($i['pago_modo']) ?> <?= $i['pago_ok']?'✅':'❌' ?> <?= $i['pago_ref'] ? '· '.htmlspecialchars($i['pago_ref']) : '' ?></td>
          <td><?= htmlspecialchars($i['status']) ?></td>
          <td class="text-end">
            <form method="post" class="d-inline" action="#" onsubmit="return false"><!-- ganchos futuros --></form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
