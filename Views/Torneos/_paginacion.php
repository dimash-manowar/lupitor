<?php
$m=$data['meta']??['page'=>1,'per'=>9,'total'=>0,'total_pages'=>1];
$base = $data['base_url'] ?? (BASE_URL.'Torneos/index/');
$qparams=['modalidad'=>$data['modalidad']??'','q'=>$data['q']??'','orden'=>$data['orden']??'proximos','per'=>$m['per']??9];
?>
<nav class="mt-2">
  <ul class="pagination pagination-sm">
    <?php for($p=1;$p<=($m['total_pages']??1);$p++): ?>
      <li class="page-item <?= ($p==($m['page']??1))?'active':'' ?>">
        <a class="page-link js-tpage" href="<?= $base.$p.'?'.http_build_query($qparams) ?>" data-page="<?= $p ?>"><?= $p ?></a>
      </li>
    <?php endfor; ?>
  </ul>
</nav>
