<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<?php $edit = !empty($m); ?>
<form class="card bg-dark text-light" method="post" enctype="multipart/form-data"
  action="<?= BASE_URL . ($edit ? 'AdminGaleria/actualizar/' . $m['id'] : 'AdminGaleria/guardar') ?>">
  <div class="card-body">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Álbum</label>
        <select name="album_id" class="form-select">
          <option value="">(Sin álbum)</option>
          <?php foreach ($albums as $al): ?>
            <option value="<?= (int)$al['id'] ?>" <?= $edit && (int)($m['album_id'] ?? 0) == $al['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($al['titulo']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <div class="col-md-6">
          <label class="form-label">Alumno (opcional)</label>
          <input name="alumno_nombre" class="form-control" maxlength="120"
            value="<?= $edit ? htmlspecialchars($m['alumno_nombre'] ?? '') : '' ?>"
            placeholder="Nombre y apellidos del alumno">
        </div>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tipo</label>
        <select name="tipo" id="tipo" class="form-select">
          <option value="imagen" <?= !$edit || $m['tipo'] === 'imagen' ? 'selected' : '' ?>>Imagen</option>
          <option value="video" <?= $edit && $m['tipo'] === 'video' ? 'selected' : '' ?>>Vídeo</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Orden</label>
        <input name="orden" type="number" class="form-control" value="<?= $edit ? (int)$m['orden'] : 1 ?>">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" name="visible" id="vis"
            <?= (!$edit || (int)($m['visible'] ?? 1) === 1) ? 'checked' : '' ?>>
          <label class="form-check-label" for="vis">Visible</label>
        </div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Título</label>
        <input name="titulo" class="form-control" required maxlength="140"
          value="<?= $edit ? htmlspecialchars($m['titulo']) : '' ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Descripción</label>
        <input name="descripcion" class="form-control" maxlength="255"
          value="<?= $edit ? htmlspecialchars($m['descripcion'] ?? '') : '' ?>">
      </div>

      <!-- Bloque IMAGEN -->
      <div class="col-12 tipo-imagen">
        <label class="form-label">Imagen (jpg/png/webp)</label>
        <input type="file" name="archivo" class="form-control" accept="image/*">
        <?php if ($edit && !empty($m['archivo_path'])): ?>
          <img src="<?= BASE_URL . $m['archivo_path'] ?>" class="mt-2 rounded" style="max-height:120px">
        <?php endif; ?>
      </div>

      <!-- Bloque VIDEO -->
      <div class="col-md-6 tipo-video">
        <label class="form-label">YouTube ID (opcional)</label>
        <input name="youtube_id" class="form-control" value="<?= $edit ? htmlspecialchars($m['youtube_id'] ?? '') : '' ?>" placeholder="Ej: dQw4w9WgXcQ">
      </div>
      <div class="col-md-6 tipo-video">
        <label class="form-label">o subir vídeo (mp4/webm)</label>
        <input type="file" name="video" class="form-control" accept="video/mp4,video/webm">
        <?php if ($edit && !empty($m['video_path'])): ?>
          <div class="mt-2 text-secondary small"><?= htmlspecialchars($m['video_path']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="card-footer d-flex justify-content-between">
    <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>AdminGaleria/index">Volver</a>
    <button class="btn btn-primary"><?= $edit ? 'Guardar cambios' : 'Crear' ?></button>
  </div>
</form>

<script>
  (function() {
    const tipoSel = document.getElementById('tipo');
    const show = () => {
      document.querySelectorAll('.tipo-imagen').forEach(el => el.style.display = (tipoSel.value === 'imagen') ? 'block' : 'none');
      document.querySelectorAll('.tipo-video').forEach(el => el.style.display = (tipoSel.value === 'video') ? 'block' : 'none');
    };
    tipoSel.addEventListener('change', show);
    show();
  })();
</script>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>