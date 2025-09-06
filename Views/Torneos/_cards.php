<?php $items = $items ?? ($data['items'] ?? []); if(!is_iterable($items)) $items=[]; ?>
<div class="news-masonry">
  <?php foreach ($items as $t): ?>
    <?php
      $f = $t['inicio'] ? date('d/m/Y', strtotime($t['inicio'])) : '';
      $h1= $t['inicio'] ? date('H:i', strtotime($t['inicio'])) : '';
      $h2= $t['fin']    ? date('H:i', strtotime($t['fin']))    : '';
      $precio = (float)($t['precio'] ?? 0);
    ?>
    <article class="nitem card bg-dark border-0 shadow-sm">
      <?php if (!empty($t['portada'])): ?>
        <div class="ratio ratio-16x9">
          <img src="<?= BASE_URL.$t['portada'] ?>" class="w-100 h-100" style="object-fit:cover" alt="<?= htmlspecialchars($t['titulo']) ?>">
        </div>
      <?php endif; ?>
      <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="badge bg-primary"><?= htmlspecialchars(ucfirst($t['modalidad'])) ?></span>
          <small class="text-secondary"><?= $f ?> <?= $h1 ? "· $h1" : "" ?> <?= $h2 ? "– $h2" : "" ?></small>
        </div>
        <h2 class="h5">
          <a class="link-light text-decoration-none" href="<?= BASE_URL.'Torneos/ver/'.urlencode($t['slug']) ?>">
            <?= htmlspecialchars($t['titulo']) ?>
          </a>
        </h2>
        <?php if (!empty($t['lugar'])): ?><div class="small text-secondary mb-1"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($t['lugar']) ?></div><?php endif; ?>
        <?php if (!empty($t['resumen'])): ?><p class="text-secondary mb-3 line-clamp-3"><?= htmlspecialchars($t['resumen']) ?></p><?php endif; ?>
        <div class="mt-auto d-flex gap-2">
          <?php if (!empty($t['bases_pdf'])): ?>
            <a class="btn btn-outline-light btn-sm" target="_blank" rel="noopener" href="<?= BASE_URL.$t['bases_pdf'] ?>">Bases</a>
          <?php endif; ?>
          <a class="btn btn-primary btn-sm" href="<?= BASE_URL.'Torneos/ver/'.urlencode($t['slug']) ?>">Inscribirse</a>
          <?php if ($precio>0): ?><span class="badge text-bg-dark ms-auto">€<?= number_format($precio,2,',','.') ?></span><?php endif; ?>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>
