<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<main class="container py-5">
  <nav class="mb-3 d-flex gap-2">
    <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-light"><i class="bi bi-house-door"></i> Inicio</a>
    <a href="<?= BASE_URL ?>Club" class="btn btn-sm btn-outline-secondary"><i class="bi bi-people"></i> Club</a>
  </nav>

  <div class="d-flex justify-content-between align-items-end mb-3">
    <div>
      <h1 class="h3 m-0"><?= htmlspecialchars($data['titulo'] ?? 'Personas') ?></h1>
      <small class="text-secondary">Miembros del club</small>
    </div>
  </div>

  <!-- Filtros -->
  <form id="people-form" class="row g-2 mb-3" method="get" action=""
        data-base="<?= htmlspecialchars($data['base_url'] ?? (BASE_URL.'Club/personas/')) ?>">
    <div class="col-sm-6">
      <input type="search" name="q" class="form-control" placeholder="Buscar por nombre/apellidos…"
             value="<?= htmlspecialchars($data['q'] ?? '') ?>">
    </div>
    <div class="col-sm-3">
      <?php $orden = $data['orden'] ?? 'orden'; ?>
      <select name="orden" class="form-select">
        <option value="orden"        <?= $orden==='orden'?'selected':'' ?>>Orden</option>
        <option value="nombre_asc"  <?= $orden==='nombre_asc'?'selected':'' ?>>Nombre (A→Z)</option>
        <option value="nombre_desc" <?= $orden==='nombre_desc'?'selected':'' ?>>Nombre (Z→A)</option>
        <option value="recientes"   <?= $orden==='recientes'?'selected':'' ?>>Recientes</option>
      </select>
    </div>
    <div class="col-sm-2">
      <?php $per = (int)($data['meta']['per'] ?? 12); ?>
      <select name="per" class="form-select">
        <?php foreach ([6,9,12,18,24] as $pp): ?>
          <option value="<?= $pp ?>" <?= $per===$pp?'selected':'' ?>><?= $pp ?>/pág</option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-sm-1 d-grid"><button class="btn btn-primary">Filtrar</button></div>
  </form>

  <!-- Contador -->
  <?php $m=$data['meta']??['page'=>1,'per'=>12,'total'=>0,'total_pages'=>1];
        $ini = ($m['total']>0) ? (($m['page']-1)*$m['per']+1) : 0;
        $fin = min($m['page']*$m['per'],$m['total']); ?>
  <div id="people-counter" class="small text-secondary mb-2">
    <?= $m['total']>0 ? "Mostrando {$ini}–{$fin} de {$m['total']} perfiles" : 'No hay resultados' ?>
  </div>

  <!-- Listado Masonry -->
  <div id="people-list">
    <?php $peopleItems = $data['items'] ?? []; require BASE_PATH.'Views/Club/_personas_cards.php'; ?>
  </div>

  <!-- Paginación -->
  <div id="people-pag">
    <?php require BASE_PATH.'Views/Club/_personas_paginacion.php'; ?>
  </div>
</main>

<!-- Modal foto (reutiliza el de tu versión anterior) -->
<div class="modal fade" id="personPhotoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content bg-dark">
      <button type="button" class="btn-close btn-close-white ms-auto me-2 mt-2" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      <img id="personPhotoImg" src="" alt="" class="w-100 rounded-bottom">
    </div>
  </div>
</div>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>
