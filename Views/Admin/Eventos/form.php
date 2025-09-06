<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<?php $isEdit = !empty($e);
$action = $isEdit ? (BASE_URL . 'AdminEventos/actualizar/' . (int)$e['id']) : (BASE_URL . 'AdminEventos/guardar'); ?>
<h1 class="h4 text-light"><?= $isEdit ? 'Editar evento' : 'Nuevo evento' ?></h1>
<form action="<?= $action ?>" method="post" class="row g-3">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="col-md-6">
        <label class="form-label text-secondary">Título</label>
        <input name="titulo" class="form-control bg-dark text-light border-secondary" required value="<?= htmlspecialchars($e['titulo'] ?? '') ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label text-secondary">Tipo</label>
        <select name="tipo" class="form-select bg-dark text-light border-secondary">
            <?php foreach (['quedada', 'torneo', 'clase', 'otro'] as $t): ?>
                <option value="<?= $t ?>" <?= (($e['tipo'] ?? 'otro') === $t ? 'selected' : '') ?>><?= ucfirst($t) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label text-secondary">Estado</label>
        <select name="estado" class="form-select bg-dark text-light border-secondary">
            <?php foreach (['publicado', 'borrador', 'cancelado'] as $st): ?>
                <option value="<?= $st ?>" <?= (($e['estado'] ?? 'publicado') === $st ? 'selected' : '') ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label text-secondary">Inicio</label>
        <input type="datetime-local" name="inicio" class="form-control bg-dark text-light border-secondary" required value="<?= isset($e['inicio']) ? date('Y-m-d\TH:i', strtotime($e['inicio'])) : '' ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label text-secondary">Fin</label>
        <input type="datetime-local" name="fin" class="form-control bg-dark text-light border-secondary" value="<?= isset($e['fin']) ? date('Y-m-d\TH:i', strtotime($e['fin'])) : '' ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label text-secondary">Lugar</label>
        <input name="lugar" class="form-control bg-dark text-light border-secondary" value="<?= htmlspecialchars($e['lugar'] ?? '') ?>">
    </div>
    <div class="col-12">
        <label class="form-label text-secondary">Descripción</label>
        <textarea name="descripcion" rows="4" class="form-control bg-dark text-light border-secondary"><?= htmlspecialchars($e['descripcion'] ?? '') ?></textarea>
    </div>
    <div class="col-12">
        <button class="btn btn-warning">Guardar</button>
        <a href="<?= BASE_URL ?>AdminEventos/index" class="btn btn-outline-light">Cancelar</a>
    </div>
</form>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>