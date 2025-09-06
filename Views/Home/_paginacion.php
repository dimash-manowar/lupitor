<?php
$m    = $data['meta'] ?? ['page'=>1,'total_pages'=>1,'per'=>9,'total'=>0];
$base = $data['base_url'] ?? (BASE_URL.'Home/index/');
$qs   = http_build_query(array_filter([
  'nivel' => $data['nivel'] ?? '',
  'q'     => $data['q'] ?? '',
  'orden' => $data['orden'] ?? 'recientes',
  'per'   => $m['per'] ?? 9,
]));
?>
<nav aria-label="Paginación">
  <ul class="pagination justify-content-center">
    <li class="page-item <?= ($m['page']??1)<=1 ? 'disabled':'' ?>">
      <a class="page-link" href="<?= $base . max(1, ($m['page']??1)-1) . ($qs?('?'.$qs):'') ?>">Anterior</a>
    </li>
    <?php for ($p=1; $p<=($m['total_pages']??1); $p++): ?>
      <li class="page-item <?= $p==($m['page']??1) ? 'active':'' ?>">
        <a class="page-link" href="<?= $base . $p . ($qs?('?'.$qs):'') ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= ($m['page']??1) >= ($m['total_pages']??1) ? 'disabled':'' ?>">
      <a class="page-link" href="<?= $base . min(($m['total_pages']??1), ($m['page']??1)+1) . ($qs?('?'.$qs):'') ?>">Siguiente</a>
    </li>
  </ul>
</nav>
