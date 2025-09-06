<?php require BASE_PATH . 'Views/Usuario/Templates/headerUsuario.php'; ?>


<div class="container my-3">

  <div class="row g-3">
    <!-- Próximo torneo -->
    <div class="col-12 col-md-6 col-xl-3">
      <div class="u-card h-100">
        <h5 class="mb-1">Próximo torneo</h5>
        <?php if (!empty($proximo)): ?>
          <div class="text-secondary mb-1"><?= date('d/m/Y H:i', strtotime($proximo['inicio'])) ?></div>
          <div class="fw-semibold"><?= htmlspecialchars($proximo['titulo']) ?></div>
          <?php if (!empty($proximo['lugar'])): ?>
            <div class="text-secondary small"><?= htmlspecialchars($proximo['lugar']) ?></div>
          <?php endif; ?>
          <div class="d-flex gap-2 mt-2">
            <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL.'Torneos/ver/'.urlencode($proximo['slug']) ?>">Detalles</a>
            <?php if (!empty($proximo['checkin_token'])): ?>
              <a class="btn btn-sm btn-outline-info" target="_blank" href="<?= BASE_URL.'Inscripcion/qr?token='.urlencode($proximo['checkin_token']) ?>"><i class="bi bi-qr-code"></i> QR</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="text-secondary">No tienes torneos próximos.</div>
          <a class="btn btn-sm btn-outline-light mt-2" href="<?= BASE_URL ?>Torneos">Buscar torneos</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Última inscripción (recibo) -->
    <div class="col-12 col-md-6 col-xl-3">
      <div class="u-card h-100">
        <h5 class="mb-1">Última inscripción</h5>
        <?php if (!empty($ultima)): ?>
          <div class="fw-semibold"><?= htmlspecialchars($ultima['titulo']) ?></div>
          <div class="text-secondary small mb-2">Inscrito: <?= date('d/m/Y H:i', strtotime($ultima['created_at'])) ?></div>
          <div class="d-flex gap-2">
            <a class="btn btn-sm btn-outline-light" target="_blank" href="<?= BASE_URL.'UsuarioInscripciones/pdf/'.(int)$ultima['id'] ?>"><i class="bi bi-file-earmark-pdf"></i> Recibo</a>
            <a class="btn btn-sm btn-outline-secondary" href="<?= BASE_URL.'UsuarioInscripciones/index' ?>">Ver todas</a>
          </div>
        <?php else: ?>
          <div class="text-secondary">Sin inscripciones registradas.</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Mis ejercicios -->
    <div class="col-12 col-md-6 col-xl-3">
      <div class="u-card h-100">
        <h5 class="mb-1">Ejercicios</h5>
        <div class="text-secondary">Accede a los ejercicios asignados por tu profesor.</div>
        <a class="btn btn-sm btn-outline-light mt-2" href="<?= BASE_URL ?>UsuarioEjercicios/index"><i class="bi bi-lightning"></i> Abrir ejercicios</a>
      </div>
    </div>

    <!-- Accesos rápidos -->
    <div class="col-12 col-md-6 col-xl-3">
      <div class="u-card h-100">
        <h5 class="mb-1">Accesos rápidos</h5>
        <div class="d-grid gap-2">
          <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>UsuarioPerfil/index"><i class="bi bi-person-circle"></i> Mi perfil</a>
          <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>UsuarioInscripciones/index"><i class="bi bi-receipt"></i> Mis inscripciones</a>
          <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>Galeria"><i class="bi bi-images"></i> Galería</a>
        </div>
      </div>
    </div>
  </div>

</div>
<?php require BASE_PATH.'Views/Usuario/Templates/footerUsuario.php'; ?>
