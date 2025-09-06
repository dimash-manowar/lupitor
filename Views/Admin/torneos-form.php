<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; $t=$data['t']??[]; ?>
<div class="container-fluid">
  <h1 class="h4 mb-3"><?= htmlspecialchars($data['titulo'] ?? 'Torneo') ?></h1>
  <form method="post" enctype="multipart/form-data"
        action="<?= isset($t['id']) ? BASE_URL.'AdminTorneos/actualizar/'.$t['id'] : BASE_URL.'AdminTorneos/guardar' ?>">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">

    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Título</label><input name="titulo" class="form-control" required value="<?= htmlspecialchars($t['titulo']??'') ?>"></div>
      <div class="col-md-3">
        <label class="form-label">Modalidad</label>
        <select name="modalidad" class="form-select">
          <?php $mod=$t['modalidad']??'otro'; foreach(['clásico','rápidas','blitz','escolar','otro'] as $m): ?>
            <option value="<?= $m ?>" <?= $mod===$m?'selected':'' ?>><?= ucfirst($m) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3"><label class="form-label">Estado</label>
        <select name="estado" class="form-select">
          <?php $es=$t['estado']??'publicado'; foreach(['publicado','borrador','cancelado'] as $e): ?>
            <option value="<?= $e ?>" <?= $es===$e?'selected':'' ?>><?= ucfirst($e) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3"><label class="form-label">Inicio</label><input type="datetime-local" name="inicio" class="form-control" value="<?= !empty($t['inicio'])?date('Y-m-d\TH:i',strtotime($t['inicio'])):'' ?>"></div>
      <div class="col-md-3"><label class="form-label">Fin</label><input type="datetime-local" name="fin" class="form-control" value="<?= !empty($t['fin'])?date('Y-m-d\TH:i',strtotime($t['fin'])):'' ?>"></div>
      <div class="col-md-6"><label class="form-label">Lugar</label><input name="lugar" class="form-control" value="<?= htmlspecialchars($t['lugar']??'') ?>"></div>

      <div class="col-md-3"><label class="form-label">Precio (€)</label><input type="number" step="0.01" name="precio" class="form-control" value="<?= htmlspecialchars((string)($t['precio']??'0')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Cupo</label><input type="number" name="cupo" class="form-control" value="<?= htmlspecialchars((string)($t['cupo']??'')) ?>"></div>
      <div class="col-md-3 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="form_activo" id="form_activo" <?= !empty($t['form_activo'])?'checked':'' ?>><label class="form-check-label" for="form_activo"> Inscripciones activas</label></div></div>

      <div class="col-12"><label class="form-label">Resumen</label><textarea name="resumen" class="form-control" rows="2"><?= htmlspecialchars($t['resumen']??'') ?></textarea></div>
      <div class="col-12"><label class="form-label">Descripción (HTML)</label><textarea name="descripcion" class="form-control" rows="8"><?= htmlspecialchars($t['descripcion']??'') ?></textarea></div>

      <div class="col-md-6"><label class="form-label">Portada (img)</label><input type="file" name="portada" class="form-control"><?php if(!empty($t['portada'])): ?><img src="<?= BASE_URL.$t['portada'] ?>" class="img-fluid rounded mt-2" style="max-height:120px;object-fit:cover;"><?php endif; ?></div>
      <div class="col-md-6"><label class="form-label">Bases (PDF)</label><input type="file" name="bases_pdf" class="form-control"><?php if(!empty($t['bases_pdf'])): ?><a target="_blank" rel="noopener" class="d-block mt-2 small" href="<?= BASE_URL.$t['bases_pdf'] ?>">Ver bases actuales</a><?php endif; ?></div>

      <div class="col-12 d-flex justify-content-end">
        <a href="<?= BASE_URL ?>AdminTorneos/index" class="btn btn-outline-secondary me-2">Volver</a>
        <button class="btn btn-primary">Guardar</button>
      </div>
    </div>
  </form>
</div>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
