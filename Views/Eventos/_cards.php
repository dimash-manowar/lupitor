<?php
$evtItems = $evtItems ?? ($data['items'] ?? []);
if (!is_iterable($evtItems)) $evtItems = [];
?>
<div class="evt-masonry">
  <?php foreach ($evtItems as $e): ?>
    <article class="mitem card bg-dark border-0 shadow-sm">
      <?php if (!empty($e['portada'])): ?>
        <div class="ratio ratio-16x9">
          <img src="<?= BASE_URL . $e['portada'] ?>" alt="<?= htmlspecialchars($e['titulo']) ?>"
               class="w-100 h-100" style="object-fit:cover;">
        </div>
      <?php endif; ?>
      <div class="card-body d-flex flex-column">
        <div class="d-flex align-items-center gap-2 small text-secondary mb-2">
          <i class="bi bi-calendar-event"></i>
          <?php
            $fecha = !empty($e['fecha']) ? date('d/m/Y', strtotime($e['fecha'])) : '';
            $hora  = !empty($e['hora'])  ? substr($e['hora'], 0, 5) : '';
          ?>
          <span><?= $fecha ?><?= $hora ? " · $hora" : "" ?></span>
        </div>

        <h2 class="h5 mb-2 line-clamp-2">
          <a class="link-light text-decoration-none stretched-link"
             href="<?= BASE_URL ?>Eventos/ver/<?= urlencode($e['slug'] ?? (string)$e['id']) ?>">
            <?= htmlspecialchars($e['titulo']) ?>
          </a>
        </h2>

        <?php if (!empty($e['lugar'])): ?>
          <div class="small text-secondary mb-2"><i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['lugar']) ?></div>
        <?php endif; ?>

        <?php if (!empty($e['resumen'])): ?>
          <p class="text-secondary mb-3 line-clamp-3"><?= htmlspecialchars($e['resumen']) ?></p>
        <?php endif; ?>

        <div class="mt-auto d-flex flex-wrap gap-2">
          <a class="btn btn-outline-light btn-sm"
             href="<?= BASE_URL ?>Eventos/ver/<?= urlencode($e['slug'] ?? (string)$e['id']) ?>">Detalles</a>
          <?php if (!empty($e['bases_pdf'])): ?>
            <a class="btn btn-outline-secondary btn-sm" target="_blank" rel="noopener"
               href="<?= BASE_URL . ltrim($e['bases_pdf'],'/') ?>">Bases</a>
          <?php endif; ?>
          <?php if (!empty($e['form_activo'])): ?>
            <a class="btn btn-primary btn-sm"
               href="<?= BASE_URL ?>Eventos/inscribirse/<?= (int)$e['id'] ?>">Inscribirme</a>
          <?php endif; ?>
        </div>
      </div>
    </article>
  <?php endforeach; ?>
</div>
