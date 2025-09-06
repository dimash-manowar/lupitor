<?php
$m = $data['meta'] ?? ['page'=>1,'per'=>9,'total'=>0,'total_pages'=>1];
$base = $data['base_url'] ?? (BASE_URL . 'Eventos/index/');
$qparams = [
  'modalidad' => $data['modalidad'] ?? '',
  'q'         => $data['q'] ?? '',
  'desde'     => $data['desde'] ?? '',
  'hasta'     => $data['hasta'] ?? '',
  'orden'     => $data['orden'] ?? 'proximos',
];
?>
<nav class="mt-2">
  <ul class="pagination pagination-sm">
    <?php for ($p=1; $p <= ($m['total_pages'] ?? 1); $p++): ?>
      <li class="page-item <?= ($p == ($m['page'] ?? 1)) ? 'active' : '' ?>">
        <a class="page-link js-e-page"
           href="<?= $base.$p.'?'.http_build_query($qparams) ?>"
           data-page="<?= $p ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
