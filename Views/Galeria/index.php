<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<h1 class="h3 text-light mb-3">Galería</h1>
<div class="row g-3">
  <?php foreach (($albums ?? []) as $a): ?>
    <div class="col-6 col-md-4 col-lg-3">
      <a class="card bg-dark text-light h-100 border-secondary" href="<?= BASE_URL.'Galeria/album/'.urlencode($a['slug']) ?>">
        <div class="card-body">
          <div class="fw-bold"><?= htmlspecialchars($a['titulo']) ?></div>
          <?php if (!empty($a['descripcion'])): ?>
            <div class="text-secondary small mt-1"><?= htmlspecialchars($a['descripcion']) ?></div>
          <?php endif; ?>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>
