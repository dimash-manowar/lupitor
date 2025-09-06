<?php require BASE_PATH . 'Views/Usuario/Templates/headerUsuario.php'; ?>
<?php
$asig = $asig ?? [];
$eTit = $asig['titulo'] ?? 'Ejercicio';
$nivel = $asig['nivel'] ?? '';
$fen   = $asig['fen_inicial'] ?? 'start';
$turno = ($asig['turno'] ?? 'w') === 'b' ? 'b' : 'w';
$lim   = !empty($asig['fecha_limite']) ? date('d/m/Y H:i', strtotime($asig['fecha_limite'])) : '—';
$max   = $asig['intentos_max'] !== null ? (int)$asig['intentos_max'] : null;
?>
<div class="container py-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h3 class="mb-0"><?= htmlspecialchars($eTit) ?></h3>
    <div class="small text-secondary">Nivel: <?= htmlspecialchars($nivel) ?> · Límite: <?= $lim ?></div>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card bg-dark border-secondary">
        <div class="card-header border-secondary">
          <div class="d-flex flex-wrap align-items-center gap-2">
            <button id="btnStart"  class="btn btn-secondary btn-sm">Posición inicial</button>
            <button id="btnFlip"   class="btn btn-secondary btn-sm">Girar</button>
            <button id="btnRec"    class="btn btn-outline-info btn-sm">Grabar intento</button>
            <button id="btnStop"   class="btn btn-outline-light btn-sm" disabled>Parar</button>
            <span class="ms-auto small text-secondary">Tiempo: <span id="timer">0s</span></span>
          </div>
        </div>
        <div class="card-body">
          <div id="board" class="mx-auto" style="width:100%;max-width:520px;aspect-ratio:1/1;"></div>
        </div>
      </div>

      <div class="text-end mt-2">
        <button id="btnEnviar" class="btn btn-primary" disabled>Enviar intento</button>
        <a class="btn btn-outline-light" href="<?= BASE_URL ?>UsuarioEjercicios/index">Volver</a>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card bg-dark border-secondary h-100">
        <div class="card-header border-secondary"><strong>Datos</strong></div>
        <div class="card-body">
          <div class="mb-2">Intentos máximos: <strong><?= $max !== null ? $max : 'Sin límite' ?></strong></div>
          <div class="alert alert-secondary small">
            Pulsa <em>Grabar intento</em>, realiza la secuencia y luego <em>Parar</em> para preparar el envío.
          </div>
          <div class="mb-2">
            <label class="form-label">PGN (intento actual)</label>
            <textarea id="pgn_actual" class="form-control" rows="7" readonly></textarea>
          </div>
          <?php if (!empty($intentos)): ?>
          <div class="mb-2">
            <label class="form-label">Historial</label>
            <div class="small">
              <?php foreach ($intentos as $i): ?>
                <div class="border rounded p-2 mb-2">
                  <div class="d-flex justify-content-between">
                    <span><?= date('d/m/Y H:i', strtotime($i['creado_en'])) ?></span>
                    <span class="badge bg-<?= ((int)$i['correcto']===1 ? 'success' : 'secondary') ?>">
                      <?= ((int)$i['correcto']===1 ? 'correcto' : 'fallo') ?>
                    </span>
                  </div>
                  <div class="text-truncate"><?= htmlspecialchars($i['pgn_enviado'] ?? '') ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require BASE_PATH . 'Views/Usuario/Templates/footerUsuario.php'; ?>
