<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<?php
$isEdit = !empty($data['e']);
$e      = $data['e'] ?? [];
$action = $isEdit
  ? (BASE_URL . 'Admin/ejerciciosEditarPost/' . (int)$e['id'])
  : (BASE_URL . 'Admin/ejerciciosCrearPost');

$titulo   = $e['titulo'] ?? '';
$nivel    = $e['nivel'] ?? 'Iniciación';
$pub      = isset($e['es_publico']) ? ((int)$e['es_publico'] === 1) : true;
$turnoSel = (($e['turno'] ?? 'w') === 'b') ? 'b' : 'w';

// FEN base si no hay nada:
$fenBase = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR {$turnoSel} KQkq - 0 1";
$fen     = trim((string)($e['fen_inicial'] ?? ''));
if ($fen === '' || strtolower($fen) === 'start') $fen = $fenBase;

$pgn = $e['pgn_solucion'] ?? '';
?>


<form action="<?= $action ?>" id="formGuardarEjercicio" method="post" class="row g-3">
  <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
  <?php if ($isEdit): ?>
    <input type="hidden" id="ejercicio_id" name="id" value="<?= (int)$e['id'] ?>">
  <?php endif; ?>
  <!-- Datos básicos -->
  <div class="col-md-8">
    <label class="form-label">Título *</label>
    <input name="titulo" class="form-control" value="<?= htmlspecialchars($titulo) ?>" required>
  </div>
  <div class="col-md-2">
    <label class="form-label">Nivel *</label>
    <select name="nivel" class="form-select" required>
      <?php foreach (['Iniciación', 'Intermedio', 'Avanzado'] as $n): ?>
        <option value="<?= $n ?>" <?= $nivel === $n ? 'selected' : '' ?>><?= $n ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2 d-flex align-items-end">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="es_publico" id="es_publico" <?= $pub ? 'checked' : '' ?>>
      <label class="form-check-label" for="es_publico">Público</label>
    </div>
  </div>

  <!-- Tablero + Form -->
  <div class="col-lg-7">
    <div class="card bg-dark border-secondary h-100">
      <!-- Toolbar arriba -->
      <div class="card-header border-secondary">
        <div class="d-flex flex-wrap align-items-center gap-2">
          <select name="turno" id="sideToMove" class="form-select form-select-sm w-auto">
            <option value="w" <?= (($e['turno'] ?? 'w') === 'w') ? 'selected' : '' ?>>Juegan blancas</option>
            <option value="b" <?= (($e['turno'] ?? 'w') === 'b') ? 'selected' : '' ?>>Juegan negras</option>
          </select>

          <div class="vr d-none d-md-block"></div>

          <button type="button" id="btnEdit" class="btn btn-outline-light btn-sm">Modo edición</button>
          <button type="button" id="btnRecord" class="btn btn-outline-info  btn-sm">Grabar solución</button>
          <button type="button" id="btnStart" class="btn btn-secondary     btn-sm">Posición inicial</button>
          <button type="button" id="btnClear" class="btn btn-secondary     btn-sm">Limpiar</button>
          <button type="button" id="btnFlip" class="btn btn-secondary     btn-sm">Girar</button>
          <button type="button" id="btnBack" class="btn btn-outline-light btn-sm">◀ Atrás</button>
          <button type="button" id="btnForward" class="btn btn-outline-light btn-sm">Adelante ▶</button>
          <button type="button" id="btnPasteFen" class="btn btn-outline-secondary btn-sm">Pegar FEN</button>
          <button type="button" id="btnCopyFen" class="btn btn-outline-secondary btn-sm">Copiar FEN</button>
          <button type="button" id="btnCopyPgn" class="btn btn-outline-secondary btn-sm">Copiar PGN</button>
        </div>
      </div>

      <!-- Tablero debajo de la toolbar -->
      <div class="card-body">
        <div id="board" class="mx-auto"
          data-fen="<?= htmlspecialchars($fen) ?>"
          data-turno="<?= htmlspecialchars($turnoSel) ?>"
          style="width:100%; max-width:520px; aspect-ratio:1/1;"></div>

      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <!-- Form a la derecha; queda “pegado” al hacer scroll -->
    <div class="card bg-dark border-secondary h-100 position-sticky" style="top: 1rem;">
      <div class="card-header border-secondary"><strong>Datos del ejercicio</strong></div>
      <div class="card-body">
        <div class="mb-2">
          <label class="form-label">FEN inicial *</label>
          <input id="fen_inicial" name="fen_inicial" class="form-control"
            value="<?= htmlspecialchars($fen) ?>" required>
          <small class="text-secondary">Si está vacío y el tablero en inicio, se usará la posición inicial.</small>
        </div>

        <div class="mb-2">
          <label class="form-label">Solución (PGN)</label>
          <textarea id="pgn_solucion" name="pgn_solucion" class="form-control" rows="8"
            placeholder="Se generará al grabar la solución"><?= htmlspecialchars($pgn) ?></textarea>
        </div>

        <div class="alert alert-secondary small mb-0">
          <strong>Cómo usar:</strong> 1) <em>Modo edición</em> para colocar piezas. 2) Elige quién mueve.
          3) <em>Grabar solución</em> y realiza los movimientos (usa Atrás/Adelante para revisarlos).
          4) Guarda.
        </div>
      </div>
      <div class="card-footer border-secondary text-end">
        <button class="btn btn-primary">Guardar</button>
        <a class="btn btn-outline-light" href="<?= BASE_URL ?>admin/ejercicios">Cancelar</a>
      </div>
    </div>
  </div>
</form>

<!-- Puente chess.js -->
<script type="module">
  import {
    Chess
  } from "https://cdn.jsdelivr.net/npm/chess.js@1.0.0/+esm";
  window.Chess = Chess;
</script>
<script>
  window.BASE_URL = '<?= rtrim(BASE_URL, '/') . '/' ?>';
</script>
<script src="<?= BASE_URL ?>Assets/vendor/chessboard/jquery-3.7.1.js"></script>
<script src="<?= BASE_URL ?>Assets/vendor/chessboard/chessboard-1.0.0.min.js"></script>
<script src="<?= BASE_URL ?>Assets/js/admin-ejercicios-form.js"></script>

<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>