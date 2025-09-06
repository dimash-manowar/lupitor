<?php
$uid = (int)($_SESSION['user']['id'] ?? 0);
foreach ($msgs as $m):
  $own = ((int)($m['sender_id'] ?? 0) === $uid);
?>
  <div class="d-flex <?= $own?'justify-content-end':'' ?> mb-2">
    <div class="bubble <?= $own?'own':'' ?>">
      <?php if (!empty($m['body'])): ?>
        <div><?= nl2br(htmlspecialchars($m['body'])) ?></div>
      <?php endif; ?>

      <?php
      $mid = (int)$m['id'];
      $adj = $atts[$mid] ?? [];
      foreach ($adj as $a):
        $isImg = str_starts_with((string)$a['mime'],'image/');
        $isVid = str_starts_with((string)$a['mime'],'video/');
        $url   = BASE_URL . ltrim($a['path'],'/');
      ?>
        <div class="mt-2">
          <?php if ($isImg): ?>
            <img class="msg-img" src="<?= $url ?>" alt="">
          <?php elseif ($isVid): ?>
            <video class="msg-video" controls preload="metadata">
              <source src="<?= $url ?>" type="<?= htmlspecialchars($a['mime']) ?>">
            </video>
          <?php else: ?>
            <a class="link-light" href="<?= $url ?>" target="_blank" rel="noopener">Descargar adjunto</a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
      <div class="text-secondary small mt-1"><?= date('d/m/Y H:i', strtotime($m['creado_en'])) ?></div>
    </div>
  </div>
<?php endforeach; ?>
