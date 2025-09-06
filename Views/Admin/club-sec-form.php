<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container-fluid">
  <h1 class="h4 mb-3"><?= htmlspecialchars($data['titulo'] ?? 'Sección') ?></h1>
  <?php $s=$data['sec']??[]; ?>
  <form method="post" enctype="multipart/form-data"
        action="<?= isset($s['id']) ? BASE_URL.'AdminClubSecciones/actualizar/'.$s['id'] : BASE_URL.'AdminClubSecciones/guardar' ?>">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">

    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Tipo</label>
        <select name="tipo" class="form-select">
          <?php $t=$s['tipo']??'info'; foreach(['historia'=>'Historia','info'=>'Info','otra'=>'Otra'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $t===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Título</label>
        <input name="titulo" class="form-control" required value="<?= htmlspecialchars($s['titulo']??'') ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Orden</label>
        <input name="orden" type="number" class="form-control" value="<?= (int)($s['orden']??0) ?>">
      </div>

      <div class="col-12">
        <label class="form-label">Resumen</label>
        <textarea name="resumen" class="form-control" rows="2"><?= htmlspecialchars($s['resumen']??'') ?></textarea>
      </div>

      <div class="col-12">
        <label class="form-label">Contenido (HTML)</label>
        <textarea name="cuerpo_html" class="form-control" rows="10"><?= htmlspecialchars($s['cuerpo_html']??'') ?></textarea>
      </div>

      <div class="col-md-6">
        <label class="form-label">Portada</label>
        <input type="file" name="portada" class="form-control">
        <?php if (!empty($s['portada'])): ?>
          <img src="<?= BASE_URL.$s['portada'] ?>" class="img-fluid rounded mt-2" style="max-height:120px;object-fit:cover;">
        <?php endif; ?>
      </div>

      <div class="col-md-3">
        <label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <?php $es=$s['estado']??'publicado'; foreach(['publicado'=>'Publicado','borrador'=>'Borrador'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $es===$k?'selected':'' ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-12 d-flex justify-content-end">
        <a href="<?= BASE_URL ?>AdminClubSecciones/index" class="btn btn-outline-secondary me-2">Volver</a>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </form>
</div>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
