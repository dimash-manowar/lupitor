<?php
/** @var array $nav_user */
$nav_user = $nav_user ?? []; // la vista espera que se lo pasen

$currPath = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');

function uhref(string $u): string {
  return (str_starts_with($u,'http')) ? $u : BASE_URL.ltrim($u,'/');
}
function uactive(string $u,string $curr): bool {
  $u=trim($u,'/'); return $u!=='' && ($curr===$u || str_starts_with($curr,$u));
}
?>
<aside class="sidebar p-2">
  <ul class="nav flex-column gap-1">
    <?php foreach ($nav_user as $n): ?>
      <li class="nav-item">
        <a class="nav-link<?= uactive((string)($n['url']??''),$currPath)?' active':'' ?>"
           href="<?= uhref((string)($n['url']??'#')) ?>">
          <?= !empty($n['icono']) ? '<i class="bi '.htmlspecialchars($n['icono']).' me-2"></i>' : '' ?>
          <?= htmlspecialchars((string)($n['titulo'] ?? '')) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</aside>
