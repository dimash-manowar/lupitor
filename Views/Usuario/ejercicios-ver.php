<?php $e = $e ?? []; ?>
<h1 class="h5 text-light mb-3"><?= htmlspecialchars($e['titulo'] ?? '') ?></h1>
<div class="card bg-dark text-light">
  <div class="card-body">
    <?php if (!empty($e['descripcion'])): ?>
      <p class="text-secondary"><?= nl2br(htmlspecialchars($e['descripcion'])) ?></p>
      <hr>
    <?php endif; ?>

    <?php if (($e['tipo'] ?? '')==='video'): ?>
      <?php if (!empty($e['youtube_id'])): ?>
        <div class="ratio ratio-16x9">
          <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($e['youtube_id']) ?>" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
      <?php elseif (!empty($e['video_url'])): ?>
        <video src="<?= htmlspecialchars($e['video_url']) ?>" controls class="w-100"></video>
      <?php endif; ?>
    <?php elseif (($e['tipo'] ?? '')==='pgn'): ?>
      <pre class="bg-black text-light p-2 rounded small"><?= htmlspecialchars($e['pgn'] ?? '') ?></pre>
      <div class="alert alert-secondary mt-2">Aquí podemos integrar un visor PGN más adelante.</div>
    <?php else: ?>
      <?php if (!empty($e['fen'])): ?>
        <div class="alert alert-info">FEN: <code><?= htmlspecialchars($e['fen']) ?></code></div>
      <?php endif; ?>
      <div class="alert alert-secondary">Aquí irá el tablero interactivo y validación de solución.</div>
    <?php endif; ?>
  </div>
</div>
