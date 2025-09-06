<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container-fluid">
  <h1 class="h3 mb-3">Datos del club</h1>
  <?php $c = $data['c'] ?? []; $h = isset($c['horarios']) ? (is_array($c['horarios']) ? $c['horarios'] : json_decode($c['horarios'], true)) : []; ?>
  <form method="post" action="<?= BASE_URL ?>AdminClub/guardar" enctype="multipart/form-data" class="row g-3">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">

    <div class="col-md-6">
      <label class="form-label">Título</label>
      <input name="titulo" class="form-control" required value="<?= htmlspecialchars($c['titulo'] ?? '') ?>">
    </div>
    <div class="col-md-6">
      <label class="form-label">Subtítulo</label>
      <input name="subtitulo" class="form-control" value="<?= htmlspecialchars($c['subtitulo'] ?? '') ?>">
    </div>

    <div class="col-12">
      <label class="form-label">Descripción (HTML)</label>
      <textarea name="cuerpo_html" rows="8" class="form-control"><?= htmlspecialchars($c['cuerpo_html'] ?? '') ?></textarea>
    </div>

    <div class="col-md-6">
      <label class="form-label">Dirección</label>
      <input name="direccion" class="form-control" value="<?= htmlspecialchars($c['direccion'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($c['email'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Teléfono</label>
      <input name="telefono" class="form-control" value="<?= htmlspecialchars($c['telefono'] ?? '') ?>">
    </div>

    <div class="col-12">
      <label class="form-label">Mapa (iframe)</label>
      <input name="mapa_iframe" class="form-control" placeholder="<iframe ...>" value="<?= htmlspecialchars($c['mapa_iframe'] ?? '') ?>">
    </div>

    <div class="col-md-6">
      <label class="form-label">Portada</label>
      <input type="file" name="portada" class="form-control">
      <?php if (!empty($c['portada'])): ?>
        <img src="<?= BASE_URL.$c['portada'] ?>" class="img-fluid mt-2 rounded" style="max-height:120px;object-fit:cover;">
      <?php endif; ?>
    </div>

    <div class="col-md-6">
      <label class="form-label">Horarios</label>
      <div class="row g-2">
        <?php $dias=['lun'=>'Lunes','mar'=>'Martes','mie'=>'Miércoles','jue'=>'Jueves','vie'=>'Viernes','sab'=>'Sábado','dom'=>'Domingo'];
        foreach ($dias as $k=>$lbl): ?>
          <div class="col-6">
            <div class="input-group input-group-sm">
              <span class="input-group-text" style="width:90px;"><?= $lbl ?></span>
              <input name="h_<?= $k ?>" class="form-control" value="<?= htmlspecialchars($h[$k] ?? '') ?>" placeholder="18:00–20:00">
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="col-12 d-flex justify-content-end">
      <button class="btn btn-primary">Guardar</button>
    </div>
  </form>
</div>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
