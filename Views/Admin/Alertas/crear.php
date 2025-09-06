<?php require BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<section class="container my-4" style="max-width:760px;">
  <h2 class="h4 mb-3">Crear alerta</h2>

  <?php if (!empty($_SESSION['flash_ok'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['flash_ok']); unset($_SESSION['flash_ok']); ?></div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?></div>
  <?php endif; ?>

  <form method="post" action="<?= BASE_URL ?>AdminAlertas/crearPost">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? csrfToken()) ?>">

    <div class="mb-3">
      <label class="form-label">Título</label>
      <input type="text" name="titulo" class="form-control" required maxlength="140" placeholder="Ej.: Recordatorio de torneo social">
    </div>

    <div class="mb-3">
      <label class="form-label">Cuerpo</label>
      <textarea name="cuerpo" class="form-control" rows="5" placeholder="Detalles de la alerta…"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Enlace opcional (URL destino)</label>
      <input type="url" name="link_url" class="form-control" placeholder="https://… (opcional)">
      <div class="form-text">Se mostrará un botón “Ver detalle” en el email/alerta.</div>
    </div>

    <div class="mb-3">
      <label class="form-label">Audiencia</label>
      <select name="audiencia" class="form-select" id="audSel">
        <option value="todos">Todos</option>
        <option value="usuarios">Sólo usuarios</option>
        <option value="admins">Sólo admins</option>
        <option value="segmento">Segmento (IDs/Emails)</option>
      </select>
    </div>

    <div id="segBox" class="mb-3" style="display:none;">
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label">IDs (separados por coma/espacio)</label>
          <textarea name="ids" class="form-control" rows="2" placeholder="12, 45, 98…"></textarea>
        </div>
        <div class="col-md-6">
          <label class="form-label">Emails (separados por coma/espacio)</label>
          <textarea name="emails" class="form-control" rows="2" placeholder="ana@..., luis@..."></textarea>
        </div>
      </div>
      <div class="form-text">Se unirán ambos (IDs y emails) como destinatarios.</div>
    </div>

    <div class="form-check form-switch mb-4">
      <input class="form-check-input" type="checkbox" id="swEmail" name="enviar_email" checked>
      <label class="form-check-label" for="swEmail">Enviar también por email (respeta la preferencia del usuario)</label>
    </div>

    <button class="btn btn-primary"><i class="bi bi-megaphone"></i> Crear y enviar</button>
  </form>
</section>

<script>
  (function(){
    const sel = document.getElementById('audSel');
    const box = document.getElementById('segBox');
    const sync = ()=>{ box.style.display = (sel.value === 'segmento') ? '' : 'none'; };
    sel?.addEventListener('change', sync); sync();
  })();
</script>
<?php require BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>
