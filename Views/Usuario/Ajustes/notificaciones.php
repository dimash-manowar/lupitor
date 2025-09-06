<?php require BASE_PATH . 'Views/Usuario/Templates/headerUsuario.php'; ?>

<section class="container my-4" style="max-width:720px;">
  <h2 class="h4 mb-3">Preferencias de notificaciones</h2>

  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <form method="post" action="<?= BASE_URL ?>UsuarioAjustes/notificacionesPost">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? csrfToken()) ?>">

    <div class="form-check form-switch mb-3">
      <input class="form-check-input" type="checkbox" id="swMensajes" name="notif_email_mensajes"
             <?= !empty($prefs['notif_email_mensajes']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="swMensajes">
        Recibir emails cuando me envíen un <strong>nuevo mensaje</strong>
      </label>
      <div class="form-text">Te enviaremos un correo con un botón para iniciar sesión y ver la conversación.</div>
    </div>

    <div class="form-check form-switch mb-4">
      <input class="form-check-input" type="checkbox" id="swAlertas" name="notif_email_alertas"
             <?= !empty($prefs['notif_email_alertas']) ? 'checked' : '' ?>>
      <label class="form-check-label" for="swAlertas">
        Recibir emails de <strong>avisos del sistema</strong> (torneos, recordatorios, etc.)
      </label>
    </div>

    <button class="btn btn-primary"><i class="bi bi-save"></i> Guardar cambios</button>
  </form>
</section>

<?php require BASE_PATH . 'Views/Usuario/Templates/footer.php'; ?>
