<?php require BASE_PATH.'Views/Templates/header.php'; ?>
<main class="container py-5">
  <nav class="mb-3"><a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-house-door"></i> Inicio</a></nav>
  <h1 class="h3 mb-4">Sobre el club</h1>
  <?php $secs=$data['secciones']??[]; ?>
  <div class="row g-3">
    <?php foreach($secs as $s): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <article class="card bg-dark border-0 shadow-sm h-100">
          <?php if(!empty($s['portada'])): ?>
            <div class="ratio ratio-16x9">
              <img src="<?= BASE_URL.$s['portada'] ?>" class="w-100 h-100" style="object-fit:cover;" alt="">
            </div>
          <?php endif; ?>
          <div class="card-body d-flex flex-column">
            <span class="badge bg-secondary mb-2"><?= htmlspecialchars(ucfirst($s['tipo'])) ?></span>
            <h2 class="h5"><a class="link-light text-decoration-none" href="<?= BASE_URL ?>Club/ver/<?= urlencode($s['slug']) ?>"><?= htmlspecialchars($s['titulo']) ?></a></h2>
            <?php if(!empty($s['resumen'])): ?><p class="text-secondary line-clamp-3"><?= htmlspecialchars($s['resumen']) ?></p><?php endif; ?>
            <div class="mt-auto">
              <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>Club/ver/<?= urlencode($s['slug']) ?>">Leer</a>
            </div>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
</main>
<?php require BASE_PATH.'Views/Templates/footer.php'; ?>
