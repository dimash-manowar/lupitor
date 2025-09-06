<?php
$peopleItems = $peopleItems ?? ($data['items'] ?? []);
if (!is_iterable($peopleItems)) $peopleItems = [];
$inic = function(string $nombre='', string $apellidos=''): string {
  $a = mb_substr(trim($nombre), 0, 1);
  $b = mb_substr(trim($apellidos), 0, 1);
  return mb_strtoupper(($a.$b) ?: 'C');
};
?>
<div class="people-masonry">
  <?php foreach ($peopleItems as $p): ?>
    <article class="pitem card bg-dark border-0 shadow-sm">
      <?php if (!empty($p['foto'])): ?>
        <div class="person-thumb">
          <img src="<?= BASE_URL . $p['foto'] ?>"
               alt="<?= htmlspecialchars(trim(($p['nombre'] ?? '').' '.($p['apellidos'] ?? ''))) ?>"
               loading="lazy"
               class="js-person-photo"
               data-src="<?= BASE_URL . $p['foto'] ?>"
               style="cursor: zoom-in;">
        </div>
      <?php else: ?>
        <div class="person-thumb person-thumb--placeholder">
          <div class="avatar-fallback"><?= $inic($p['nombre'] ?? '', $p['apellidos'] ?? '') ?></div>
        </div>
      <?php endif; ?>

      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <span class="badge bg-primary text-uppercase"><?= htmlspecialchars($p['tipo'] ?? '') ?></span>
          <?php if ($p['elo'] !== null && $p['elo'] !== ''): ?>
            <small class="text-secondary">ELO: <?= (int)$p['elo'] ?></small>
          <?php endif; ?>
        </div>
        <h2 class="h5 mb-1"><?= htmlspecialchars(trim(($p['nombre'] ?? '').' '.($p['apellidos'] ?? ''))) ?></h2>
        <?php if (!empty($p['email'])): ?>
          <div class="small text-secondary mb-2"><i class="bi bi-envelope"></i> <?= htmlspecialchars($p['email']) ?></div>
        <?php endif; ?>
        <?php if (!empty($p['bio'])): ?>
          <p class="text-secondary mb-0 line-clamp-3"><?= htmlspecialchars($p['bio']) ?></p>
        <?php endif; ?>
      </div>
    </article>
  <?php endforeach; ?>
</div>
<script>
  (function(){
    const modalEl = document.getElementById('personPhotoModal');
    const imgEl   = document.getElementById('personPhotoImg');
    if (!modalEl || !imgEl) return;
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    document.querySelectorAll('.js-person-photo').forEach(img=>{
      img.addEventListener('click', ()=>{
        imgEl.src = img.getAttribute('data-src') || img.src;
        bsModal.show();
      });
    });
    modalEl.addEventListener('hidden.bs.modal', ()=>{ imgEl.src=''; });
  })();
</script>
