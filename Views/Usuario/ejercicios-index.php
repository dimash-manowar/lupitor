<h1 class="h4 text-light mb-3">Mis ejercicios</h1>

<?php if (empty($items)): ?>
  <div class="alert alert-dark border-secondary">Todavía no tienes ejercicios asignados.</div>
<?php else: ?>
<div class="row g-3">
  <?php foreach ($items as $e): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <div class="media-card h-100 d-flex flex-column">
        <div class="media-thumb">
          <?php
            $thumbIcon = match ($e['tipo']) {
              'video' => 'bi-camera-video',
              'pgn'   => 'bi-journal-code',
              'final' => 'bi-trophy',
              default => 'bi-lightning'
            };
          ?>
          <div class="d-flex align-items-center justify-content-center text-secondary" style="width:100%;height:100%;">
            <i class="bi <?= $thumbIcon ?> fs-1"></i>
          </div>
        </div>
        <div class="media-body">
          <p class="media-title text-truncate" title="<?= htmlspecialchars($e['titulo']) ?>">
            <?= htmlspecialchars($e['titulo']) ?>
          </p>
          <div class="d-flex justify-content-between align-items-center">
            <span class="media-meta">
              <?= htmlspecialchars(ucfirst($e['dificultad'])) ?> · <?= htmlspecialchars($e['tipo']) ?>
            </span>
            <a class="btn btn-sm btn-soft" href="<?= BASE_URL.'UsuarioEjercicios/ver/'.(int)$e['id'] ?>">Abrir</a>
          </div>
          <?php if (!empty($e['disponible_hasta'])): ?>
            <div class="small text-warning mt-1">
              Disponible hasta: <?= date('d/m/Y H:i', strtotime($e['disponible_hasta'])) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
