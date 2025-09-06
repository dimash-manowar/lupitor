<?php require BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <h1 class="h4 m-0">Torneos</h1>
    <a class="btn btn-primary btn-sm" href="<?= BASE_URL ?>AdminTorneos/crear"><i class="bi bi-plus-circle"></i> Nuevo</a>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-sm-2">
      <?php $e = $f['estado'] ?? ''; ?>
      <select name="estado" class="form-select">
        <?php foreach (['' => 'Estado', 'publicado' => 'Publicado', 'borrador' => 'Borrador', 'cancelado' => 'Cancelado'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= $e === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-2">
      <?php $m = $f['modalidad'] ?? ''; ?>
      <select name="modalidad" class="form-select">
        <?php foreach (['' => 'Modalidad', 'clásico' => 'Clásico', 'rápidas' => 'Rápidas', 'blitz' => 'Blitz', 'escolar' => 'Escolar', 'otro' => 'Otro'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= $m === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-4"><input name="q" class="form-control" placeholder="Buscar…" value="<?= htmlspecialchars($f['q'] ?? '') ?>"></div>
    <div class="col-sm-2">
      <?php $o = $f['orden'] ?? 'recientes'; ?>
      <select name="orden" class="form-select">
        <?php foreach (['recientes' => 'Recientes', 'fecha_asc' => 'Fecha (próx.)', 'fecha_desc' => 'Fecha (lejos)', 'titulo_asc' => 'Título (A→Z)', 'titulo_desc' => 'Título (Z→A)'] as $k => $v): ?>
          <option value="<?= $k ?>" <?= $o === $k ? 'selected' : '' ?>><?= $v ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-1">
      <?php $per = (int)($f['per'] ?? 12); ?>
      <select name="per" class="form-select">
        <?php foreach ([6, 9, 12, 18, 24] as $pp): ?><option value="<?= $pp ?>" <?= $per === $pp ? 'selected' : '' ?>><?= $pp ?>/pág</option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-1 d-grid"><button class="btn btn-outline-light">Filtrar</button></div>
  </form>

  <?php $items = $items ?? []; ?>
  <div class="table-responsive">
    <table class="table table-dark table-striped align-middle">
      <thead>
        <tr>
          <th>Título</th>
          <th>Fecha</th>
          <th>Modalidad</th>
          <th>Federado</th>
          <th>Precio</th>
          <th>Estado</th>
          <th>Inscripciones</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['titulo']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($t['inicio'])) ?></td>
            <td><?= htmlspecialchars($t['modalidad']) ?></td>
            <td>
              <?php if ($f['federado']): ?>
                <span class="badge text-bg-info">Sí</span>
              <?php else: ?>
                <span class="badge text-bg-secondary">No</span>
              <?php endif; ?>
            </td>
            <td>€<?= number_format((float)$t['precio'], 2, ',', '.') ?></td>
            <td><span class="badge <?= $t['estado'] === 'publicado' ? 'bg-success' : ($t['estado'] === 'borrador' ? 'bg-secondary' : 'bg-danger') ?>"><?= $t['estado'] ?></span></td>
            <td><a class="btn btn-sm btn-outline-light" href="<?= BASE_URL . 'AdminTorneos/inscripciones/' . $t['id'] ?>"><i class="bi bi-people"></i></a></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL . 'AdminTorneos/editar/' . $t['id'] ?>">Editar</a>
              <a class="btn btn-sm btn-outline-light"
                href="<?= BASE_URL ?>AdminInscripciones/index?torneo_id=<?= (int)$it['id'] ?>">
                <i class="bi bi-people"></i> Inscripciones
              </a>
              <form class="d-inline" method="post" action="<?= BASE_URL . 'AdminTorneos/eliminar/' . $t['id'] ?>" onsubmit="return confirm('¿Eliminar torneo?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
                <button class="btn btn-sm btn-outline-danger">Borrar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>