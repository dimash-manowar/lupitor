<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<section class="container my-4" style="max-width: 800px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="h4">Notificaciones</h2>
    <form method="post" action="<?= BASE_URL ?>Notificaciones/marcarTodas">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
      <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-all"></i> Marcar todas como leídas</button>
    </form>
  </div>

  <div class="list-group">
    <?php foreach (($items ?? []) as $n): ?>
      <div class="list-group-item d-flex align-items-start justify-content-between">
        <div>
          <div class="fw-semibold"><?= htmlspecialchars($n['titulo'] ?? '') ?></div>
          <?php if (!empty($n['cuerpo'])): ?>
            <div class="text-secondary small"><?= htmlspecialchars($n['cuerpo']) ?></div>
          <?php endif; ?>
          <div class="small text-muted"><?= date('d/m/Y H:i', strtotime($n['creada_en'])) ?></div>
          <?php if (!empty($n['link_url'])): ?>
            <a class="small" href="<?= htmlspecialchars($n['link_url']) ?>">Abrir</a>
          <?php endif; ?>
        </div>
        <form method="post" action="<?= BASE_URL ?>Notificaciones/marcarLeida">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrfToken()) ?>">
          <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
          <?php if (empty($n['leida_en'])): ?>
            <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-check2"></i></button>
          <?php else: ?>
            <span class="badge bg-secondary">Leída</span>
          <?php endif; ?>
        </form>
      </div>
    <?php endforeach; ?>
    <?php if (empty($items)): ?>
      <div class="list-group-item text-secondary">No hay notificaciones.</div>
    <?php endif; ?>
  </div>
</section>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>
