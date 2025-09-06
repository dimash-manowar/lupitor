<?php
// Acepta varias formas de entrada y asegura iterable
$newsItems = $newsItems
    ?? ($items ?? ($data['items'] ?? []));
if (!is_iterable($newsItems)) {
    $newsItems = [];
}
?>
<div class="news-masonry">
  <?php foreach ($newsItems as $n): ?>
    <article class="mitem card bg-dark border-0 shadow-sm">
      <?php if (!empty($n['portada'])): ?>
        <div class="news-thumb">
          <a href="<?= BASE_URL . 'Noticias/ver/' . urlencode($n['slug']) ?>" class="stretched-link">
            <img src="<?= BASE_URL . $n['portada'] ?>" alt="<?= htmlspecialchars($n['titulo']) ?>" loading="lazy">
          </a>
        </div>
      <?php endif; ?>
      <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($n['categoria']) ?></span>
          <?php if (!empty($n['publicado_at'])): ?>
            <small class="text-secondary"><?= date('d/m/Y', strtotime($n['publicado_at'])) ?></small>
          <?php endif; ?>
        </div>
        <h2 class="h5 mb-2 line-clamp-2">
          <a class="link-light text-decoration-none stretched-link"
             href="<?= BASE_URL . 'Noticias/ver/' . urlencode($n['slug']) ?>">
            <?= htmlspecialchars($n['titulo']) ?>
          </a>
        </h2>
        <?php if (!empty($n['resumen'])): ?>
          <p class="text-secondary mb-3 line-clamp-3"><?= htmlspecialchars($n['resumen']) ?></p>
        <?php endif; ?>
        <div class="mt-auto d-flex gap-2">
          <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL . 'Noticias/ver/' . urlencode($n['slug']) ?>">Leer</a>
          <?php if (!empty($n['portada'])): ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?= BASE_URL . 'Noticias/ver/' . urlencode($n['slug']) ?>#galeria">Galería</a>
          <?php endif; ?>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>
