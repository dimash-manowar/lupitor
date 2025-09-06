<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container my-3">
  <div class="u-card">
    <h5 class="mb-3">Ajustes de mensajería</h5>
    <form method="post" action="<?= BASE_URL ?>AdminAjustes/mensajesGuardar">
      <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
      <div class="row g-3 align-items-center">
        <div class="col-sm-4">
          <label class="form-label">Tamaño máximo de adjuntos (MB)</label>
          <input type="number" class="form-control bg-dark text-light" name="msg_max_mb"
                 min="1" max="100" step="1" value="<?= (int)($maxMb ?? 15) ?>">
          <div class="form-text text-secondary">
            Este valor se aplica a imágenes y vídeos en Mensajes.
          </div>
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-primary">Guardar</button>
      </div>
    </form>
    <hr class="my-3">
    <div class="small text-secondary">
      Nota: Si los límites de <code>php.ini</code> son más bajos que este valor,
      prevalece PHP. Comprueba <code>upload_max_filesize</code> y <code>post_max_size</code>.
    </div>
  </div>
</div>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
