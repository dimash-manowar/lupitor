<?php $items = $data['ultimas_noticias'] ?? []; if ($items): ?>
<section class="container my-5">
  <div class="d-flex justify-content-between align-items-end mb-3">
    <h2 class="h4 m-0">Últimas noticias</h2>
    <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>Noticias">Ver todas</a>
  </div>
  <div class="row g-3">
    <?php foreach ($items as $n): ?>
      <div class="col-md-4">
        <article class="card bg-dark border-0 shadow-sm h-100">
          <?php if (!empty($n['portada'])): ?>
            <img src="<?= BASE_URL . $n['portada'] ?>" class="card-img-top" alt="<?= htmlspecialchars($n['titulo']) ?>">
          <?php endif; ?>
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($n['categoria'])) ?></span>
              <small class="text-muted"><?= $n['publicado_at'] ? date('d/m/Y', strtotime($n['publicado_at'])) : '' ?></small>
            </div>
            <h3 class="h6">
              <a class="link-light" href="<?= BASE_URL.'Noticias/ver/'.urlencode($n['slug']) ?>">
                <?= htmlspecialchars($n['titulo']) ?>
              </a>
            </h3>
            <?php if (!empty($n['resumen'])): ?>
              <p class="text-secondary small mb-0"><?= htmlspecialchars($n['resumen']) ?></p>
            <?php endif; ?>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
