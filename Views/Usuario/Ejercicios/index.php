<?php require BASE_PATH . 'Views/Usuario/Templates/headerUsuario.php'; ?>
<div class="container py-3">
  <h2 class="mb-3">Mis ejercicios</h2>

  <div class="row g-3">
    <?php foreach (($items ?? []) as $it): 
      $estado = $it['estado_calc'] ?? 'pendiente';
      $badge  = $estado==='completado' ? 'success' : ($estado==='expirado' ? 'secondary' : 'warning');
      $limite = !empty($it['fecha_limite']) ? date('d/m/Y H:i', strtotime($it['fecha_limite'])) : '—';
      $usados = (int)($it['intentos_usados'] ?? 0);
      $max    = $it['intentos_max'] !== null ? (int)$it['intentos_max'] : null;
      $rest   = $max !== null ? max(0, $max - $usados) : null;
    ?>
      <div class="col-md-6">
        <div class="card bg-dark border-secondary h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h5 class="card-title mb-1"><?= htmlspecialchars($it['titulo']) ?></h5>
                <div class="small text-secondary">Nivel: <?= htmlspecialchars($it['nivel']) ?></div>
              </div>
              <span class="badge bg-<?= $badge ?> text-uppercase"><?= $estado ?></span>
            </div>

            <div class="mt-2 small">
              <div>Fecha límite: <strong><?= $limite ?></strong></div>
              <div>Intentos: 
                <?php if ($max !== null): ?>
                  <strong><?= $usados ?>/<?= $max ?><?= $rest===0 ? ' (agotados)' : '' ?></strong>
                <?php else: ?>
                  <strong><?= $usados ?></strong> usados (sin límite)
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-end gap-2">
            <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL . 'UsuarioEjercicios/resolver/' . (int)$it['id'] ?>">
              Resolver
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($items)): ?>
      <div class="col-12">
        <div class="alert alert-secondary">Aún no tienes ejercicios asignados.</div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require BASE_PATH . 'Views/Usuario/Templates/footerUsuario.php'; ?>
