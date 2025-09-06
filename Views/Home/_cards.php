

<?php foreach ($ejercicios as $ex): ?>
  <?php 
    $fen = trim(($ex['fen_inicial'] ?? '') . ' ' . ($ex['turno'] ?? 'w') . ' - - 0 1'); 
    $boardId = 'board-' . (int)$ex['id']; 
  ?>
  <div class="col-md-4 mb-4">
    <div class="card exercise-card shadow-sm h-100"
         data-fen="<?= htmlspecialchars($fen) ?>"
         data-pgn="<?= htmlspecialchars($ex['pgn_solucion'] ?? '') ?>"
         data-board-id="<?= $boardId ?>">

      <!-- Cabecera -->
      <div class="card-header text-center">
        <h5 class="mb-0"><?= htmlspecialchars($ex['titulo']) ?></h5>
        <small class="text-muted">Nivel: <?= htmlspecialchars($ex['nivel']) ?></small>
      </div>

      <!-- Tablero -->
      <div class="card-body d-flex flex-column text-center">
        <div id="<?= $boardId ?>" class="exercise-board mb-3" style="width:100%; aspect-ratio:1/1"></div>

        <!-- Botones -->
        <div class="btn-group mb-3" role="group">
          <button type="button" class="btn btn-sm btn-outline-secondary js-ex-back">Atrás</button>
          <button type="button" class="btn btn-sm btn-outline-secondary js-ex-forward">Delante</button>
          <button type="button" class="btn btn-sm btn-info js-ex-hint">Pista</button>
          <button type="button" class="btn btn-sm btn-warning js-ex-solution">Solución</button>
          <button type="button" class="btn btn-sm btn-danger js-ex-retry">Nuevo intento</button>
        </div>

        <!-- Intentos -->
        <p class="small">Número de intentos: <span class="js-ex-attempts">0</span></p>
      </div>
    </div>
  </div>

<?php endforeach; ?>
