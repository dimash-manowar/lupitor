<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="filtros-bar mb-3" data-bs-theme="dark">
  <form class="row g-2" method="get">
    <div class="col-md-3">
      <label class="form-label">Álbum</label>
      <select class="form-select" name="album">
        <option value="">Todos</option>
        <?php foreach ($albums as $al): ?>
          <option value="<?= (int)$al['id'] ?>" <?= (($f['album']??'')==$al['id']?'selected':'') ?>>
            <?= htmlspecialchars($al['titulo']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label">Tipo</label>
      <select class="form-select" name="tipo">
        <option value="">Todos</option>
        <option value="imagen" <?= (($f['tipo']??'')==='imagen'?'selected':'') ?>>Imagen</option>
        <option value="video"  <?= (($f['tipo']??'')==='video'?'selected':'') ?>>Vídeo</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Buscar</label>
      <input name="q" class="form-control" value="<?= htmlspecialchars($f['q']??'') ?>" placeholder="Título o descripción">
    </div>
    <div class="col-md-3 d-flex align-items-end gap-2">
      <a href="<?= BASE_URL ?>AdminGaleria/crear" class="btn btn-primary">Nuevo</a>
      <button class="btn btn-outline-light">Filtrar</button>
    </div>
  </form>
</div>

<div class="table-responsive">
<table class="table table-dark table-hover align-middle">
  <thead>
    <tr>
      <th>ID</th><th>Álbum</th><th>Título</th><th>Tipo</th><th>Preview</th><th>Visible</th><th>Orden</th><th class="text-end">Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach (($items??[]) as $m): ?>
      <tr>
        <td>#<?= (int)$m['id'] ?></td>
        <td><?= htmlspecialchars($m['album'] ?? '—') ?></td>
        <td><?= htmlspecialchars($m['titulo']) ?></td>
        <td><?= htmlspecialchars($m['tipo']) ?></td>
        <td>
          <?php if ($m['tipo']==='imagen' && !empty($m['archivo_path'])): ?>
            <img src="<?= BASE_URL.$m['archivo_path'] ?>" alt="" width="60" height="40" style="object-fit:cover;border-radius:6px">
          <?php elseif(!empty($m['youtube_id'])): ?>
            <span class="badge text-bg-danger">YouTube</span>
          <?php elseif(!empty($m['video_path'])): ?>
            <span class="badge text-bg-secondary">MP4</span>
          <?php endif; ?>
        </td>
        <td><?= ((int)($m['visible'] ?? 1)===1 ? 'Sí':'No') ?></td>
        <td><?= (int)($m['orden'] ?? 1) ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-info" href="<?= BASE_URL ?>AdminGaleria/editar/<?= (int)$m['id'] ?>">Editar</a>
          <form class="d-inline" method="post" action="<?= BASE_URL ?>AdminGaleria/eliminar/<?= (int)$m['id'] ?>" onsubmit="return confirm('¿Eliminar?')">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>
