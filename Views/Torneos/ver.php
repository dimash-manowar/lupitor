<?php require BASE_PATH.'Views/Templates/header.php'; $t=$data['t']; ?>
<main class="container py-5">
  <nav class="mb-3 d-flex gap-2">
    <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-house"></i> Inicio</a>
    <a href="<?= BASE_URL ?>Torneos" class="btn btn-sm btn-outline-secondary"><i class="bi bi-trophy"></i> Torneos</a>
  </nav>

  <article class="mx-auto" style="max-width: 980px;">
    <header class="mb-3 text-center">
      <div class="mb-2"><span class="badge bg-primary"><?= htmlspecialchars(ucfirst($t['modalidad'])) ?></span></div>
      <h1 class="display-6 mb-2"><?= htmlspecialchars($t['titulo']) ?></h1>
      <div class="text-secondary small">
        <?= date('d/m/Y', strtotime($t['inicio'])) ?>
        <?= ' · '.date('H:i', strtotime($t['inicio'])) ?>
        <?php if (!empty($t['fin'])): ?> – <?= date('H:i', strtotime($t['fin'])) ?><?php endif; ?>
        <?php if (!empty($t['lugar'])): ?> · <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($t['lugar']) ?><?php endif; ?>
        <?php if ((float)$t['precio']>0): ?> · <strong>€<?= number_format((float)$t['precio'],2,',','.') ?></strong><?php endif; ?>
      </div>
    </header>

    <?php if (!empty($t['portada'])): ?>
      <img src="<?= BASE_URL.$t['portada'] ?>" class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($t['titulo']) ?>">
    <?php endif; ?>

    <?php if (!empty($t['resumen'])): ?><p class="lead text-secondary"><?= htmlspecialchars($t['resumen']) ?></p><?php endif; ?>

    <div class="contenido lead"><?= $t['descripcion'] ?></div>

    <div class="mt-4 d-flex flex-wrap gap-2">
      <?php if (!empty($t['bases_pdf'])): ?>
        <a class="btn btn-outline-light" target="_blank" rel="noopener" href="<?= BASE_URL.$t['bases_pdf'] ?>"><i class="bi bi-file-earmark-pdf"></i> Bases</a>
      <?php endif; ?>
      <?php if ($t['form_activo']): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalInscripcion"
                data-torneo-id="<?= (int)$t['id'] ?>" data-precio="<?= (float)$t['precio'] ?>">Inscribirme</button>
      <?php else: ?>
        <span class="badge text-bg-secondary">Inscripciones cerradas</span>
      <?php endif; ?>
    </div>
  </article>
</main>

<!-- Modal Inscripción -->
<div class="modal fade" id="modalInscripcion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header">
        <h5 class="modal-title">Inscripción — <?= htmlspecialchars($t['titulo']) ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formInscripcion" method="post" action="<?= BASE_URL ?>Torneos/inscribirsePost">
          <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
          <input type="hidden" name="torneo_id" id="fi_evento_id" value="<?= (int)$t['id'] ?>">
          <input type="hidden" name="pago_ok" id="fi_pago_ok" value="0">
          <input type="hidden" name="pago_modo" id="fi_pago_modo" value="ninguno">
          <input type="hidden" name="pago_ref"  id="fi_pago_ref"  value="">

          <div class="row g-3">
            <div class="col-sm-6"><label class="form-label">Nombre</label><input name="nombre" class="form-control" required></div>
            <div class="col-sm-6"><label class="form-label">Apellidos</label><input name="apellidos" class="form-control"></div>
            <div class="col-sm-8"><label class="form-label">Dirección</label><input name="direccion" class="form-control"></div>
            <div class="col-sm-4"><label class="form-label">Fecha nacimiento</label><input type="date" name="fecha_nac" class="form-control"></div>
            <div class="col-sm-4"><label class="form-label">ELO</label><input name="elo" class="form-control" inputmode="numeric" pattern="\\d*"></div>
            <div class="col-sm-4"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
            <div class="col-sm-4"><label class="form-label">Teléfono</label><input name="telefono" class="form-control"></div>
          </div>

          <?php $precio=(float)$t['precio']; ?>
          <?php if ($precio>0): ?>
          <hr class="my-4">
          <div class="row g-3">
            <div class="col-12"><strong>Método de pago</strong> <small class="text-secondary">(cuota: €<?= number_format($precio,2,',','.') ?>)</small></div>
            <div class="col-md-4">
              <div class="border rounded p-3 h-100">
                <div id="paypal-button-container"></div>
                <small class="text-secondary d-block mt-2">PayPal</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="border rounded p-3 h-100">
                <label class="form-label">Bizum (código)</label>
                <input id="bizum_code" class="form-control" placeholder="Ej: BZ-123456">
                <button type="button" id="btnBizumOk" class="btn btn-outline-light btn-sm mt-2">He pagado</button>
                <small class="text-secondary d-block mt-2">Verificaremos el código</small>
              </div>
            </div>
            <div class="col-md-4">
              <div class="border rounded p-3 h-100">
                <button type="button" class="btn btn-outline-secondary w-100" disabled>Tarjeta (próximamente)</button>
                <small class="text-secondary d-block mt-2">TPV en futura iteración</small>
              </div>
            </div>
          </div>
          <?php endif; ?>

          <div class="mt-4 d-flex justify-content-end">
            <button type="submit" id="btnGuardarIns" class="btn btn-primary" <?= $precio>0 ? 'disabled' : '' ?>>Guardar inscripción</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require BASE_PATH.'Views/Templates/footer.php'; ?>
