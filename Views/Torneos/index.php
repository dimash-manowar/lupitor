<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<main class="container py-5">
    <nav class="mb-3"><a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>"><i class="bi bi-house"></i> Inicio</a></nav>

    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h1 class="h3 m-0">Torneos</h1><small class="text-secondary">Inscripciones y bases</small>
        </div>
    </div>

    <form id="torneos-form" class="row g-2 mb-3" method="get" action=""
        data-base="<?= htmlspecialchars($data['base_url'] ?? (BASE_URL . 'Torneos/index/')) ?>">
        <div class="col-sm-3">
            <?php $mod = $data['modalidad'] ?? ''; ?>
            <select name="modalidad" class="form-select">
                <?php foreach (['' => 'Todas', 'clásico' => 'Clásico', 'rápidas' => 'Rápidas', 'blitz' => 'Blitz', 'escolar' => 'Escolar', 'otro' => 'Otro'] as $k => $v): ?>
                    <option value="<?= $k ?>" <?= $mod === $k ? 'selected' : '' ?>><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-4">
            <input type="search" name="q" class="form-control" placeholder="Buscar por título o lugar…" value="<?= htmlspecialchars($data['q'] ?? '') ?>">
        </div>
        <div class="col-sm-2">
            <?php $orden = $data['orden'] ?? 'proximos'; ?>
            <select name="orden" class="form-select">
                <option value="proximos" <?= $orden === 'proximos' ? 'selected' : '' ?>>Próximos primero</option>
                <option value="fecha_desc" <?= $orden === 'fecha_desc' ? 'selected' : '' ?>>Más lejanos</option>
                <option value="titulo_asc" <?= $orden === 'titulo_asc' ? 'selected' : '' ?>>Título (A→Z)</option>
                <option value="titulo_desc" <?= $orden === 'titulo_desc' ? 'selected' : '' ?>>Título (Z→A)</option>
            </select>
        </div>
        <div class="col-sm-2">
            <?php $per = (int)($data['meta']['per'] ?? 9); ?>
            <select name="per" class="form-select">
                <?php foreach ([6, 9, 12, 18, 24] as $pp): ?>
                    <option value="<?= $pp ?>" <?= $per === $pp ? 'selected' : '' ?>><?= $pp ?>/pág</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-1 d-grid"><button class="btn btn-primary">Filtrar</button></div>
    </form>

    <?php $m = $data['meta'] ?? ['page' => 1, 'per' => 9, 'total' => 0, 'total_pages' => 1];
    $ini = ($m['total'] > 0) ? (($m['page'] - 1) * $m['per'] + 1) : 0;
    $fin = min($m['page'] * $m['per'], $m['total']); ?>
    <div id="torneos-counter" class="small text-secondary mb-2">
        <?= $m['total'] > 0 ? "Mostrando {$ini}–{$fin} de {$m['total']} torneos" : 'No hay torneos con ese filtro' ?>
    </div>

    <div id="torneos-list"><?php $items = $data['items'] ?? [];
                            require BASE_PATH . 'Views/Torneos/_cards.php'; ?></div>
    <div id="torneos-pag"><?php require BASE_PATH . 'Views/Torneos/_paginacion.php'; ?></div>
</main>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>