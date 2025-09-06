<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 m-0">Tarjetas Home</h1>
    <a class="btn btn-primary" href="<?= BASE_URL ?>AdminHomeCards/crear"><i class="bi bi-plus"></i> Nueva</a>
  </div>

  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success"><?= $_SESSION['flash_ok']; unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-dark table-striped align-middle">
      <thead>
        <tr>
          <th>Orden</th>
          <th data-id="<?= (int)$c['id'] ?>">Título</th>
          <th data-id="<?= (int)$c['id'] ?>">Descripción</th>
          <th data-id="<?= (int)$c['id'] ?>">Icono</th>
          <th data-id="<?= (int)$c['id'] ?>">Colores</th>
          <th data-id="<?= (int)$c['id'] ?>">Destino</th>
          <th data-id="<?= (int)$c['id'] ?>">Visible</th>
          <th data-id="<?= (int)$c['id'] ?>">Imagen</th>
          <th data-id="<?= (int)$c['id'] ?>" class="text-end">Acciones </th>
        </tr>
      </thead>
      <tbody id="sortable-body" data-ajax="<?= BASE_URL ?>AdminHomeCards/ordenarAjax" data-csrf="<?= csrfToken() ?>">
        <?php foreach ($data['cards'] as $c): ?>
          <tr>
            <td style="width:42px" class="text-center cursor-grab"><i class="bi bi-list fs-5"></i>
              <form action="<?= BASE_URL ?>AdminHomeCards/ordenar/<?= (int)$c['id'] ?>" method="post" class="d-flex gap-2">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <input type="number" class="form-control form-control-sm" name="orden" value="<?= (int)$c['orden'] ?>">
                <button class="btn btn-sm btn-outline-light">OK</button>
              </form>
            </td>
            <td class="fw-semibold"><?= htmlspecialchars($c['titulo']) ?></td>
            <td class="text-secondary small"><?= htmlspecialchars($c['descripcion']) ?></td>
            <td><i class="bi <?= htmlspecialchars($c['icono']) ?>"></i> <small><?= htmlspecialchars($c['icono']) ?></small></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <span class="badge" style="background:<?= htmlspecialchars($c['color_fondo']) ?>;color:<?= htmlspecialchars($c['color_texto']) ?>">Aa</span>
                <small><?= htmlspecialchars($c['color_fondo']) ?> / <?= htmlspecialchars($c['color_texto']) ?></small>
              </div>
            </td>
            <td><code><?= htmlspecialchars($c['destino']) ?></code></td>
            <td>
              <?php if ($c['visible']): ?>
                <a class="badge bg-success text-decoration-none" href="<?= BASE_URL ?>AdminHomeCards/visible/<?= (int)$c['id'] ?>/0">Sí</a>
              <?php else: ?>
                <a class="badge bg-secondary text-decoration-none" href="<?= BASE_URL ?>AdminHomeCards/visible/<?= (int)$c['id'] ?>/1">No</a>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($c['imagen'])): ?>
                <img src="<?= BASE_URL . $c['imagen'] ?>" alt="" class="rounded" style="width:60px;height:40px;object-fit:cover">
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>AdminHomeCards/editar/<?= (int)$c['id'] ?>">Editar</a>
              <form action="<?= BASE_URL ?>AdminHomeCards/borrar/<?= (int)$c['id'] ?>" method="post" class="d-inline" onsubmit="return confirm('¿Borrar tarjeta?');">
                <input type="hidden" name="csrf" value="<?= csrfToken() ?>">
                <button class="btn btn-sm btn-outline-danger">Borrar</button>
              </form>
            </td>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
