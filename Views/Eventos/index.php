<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<main class="container py-5">

    <nav class="mb-3">
        <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-light">
            <i class="bi bi-house-door"></i> Inicio
        </a>
    </nav>

    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h1 class="h3 m-0">Próximos eventos</h1>
            <small class="text-secondary">Torneos y actividades del club</small>
        </div>
    </div>

    <!-- Filtros -->
    <form id="evt-form" class="row g-2 mb-3" method="get" action="" data-base="<?= BASE_URL ?>Eventos/index/">
        <div class="col-sm-3">
            <?php $mod = $data['modalidad'] ?? ''; ?>
            <select name="modalidad" class="form-select">
                <option value="">Todas las modalidades</option>
                <?php foreach (['Clásico', 'Rápido', 'Blitz'] as $m): ?>
                    <option value="<?= $m ?>" <?= $mod === $m ? 'selected' : '' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-3">
            <input type="search" name="q" class="form-control" placeholder="Buscar por título o lugar…"
                value="<?= htmlspecialchars($data['q'] ?? '') ?>">
        </div>
        <div class="col-sm-2">
            <input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($data['desde'] ?? '') ?>" title="Desde">
        </div>
        <div class="col-sm-2">
            <input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($data['hasta'] ?? '') ?>" title="Hasta">
        </div>
        <div class="col-sm-1">
            <?php $orden = $data['orden'] ?? 'proximos'; ?>
            <select name="orden" class="form-select" title="Orden">
                <option value="proximos" <?= $orden === 'proximos'   ? 'selected' : '' ?>>Más próximos</option>
                <option value="fecha_desc" <?= $orden === 'fecha_desc' ? 'selected' : '' ?>>Fecha (recientes)</option>
                <option value="titulo_asc" <?= $orden === 'titulo_asc' ? 'selected' : '' ?>>Título (A→Z)</option>
                <option value="titulo_desc" <?= $orden === 'titulo_desc' ? 'selected' : '' ?>>Título (Z→A)</option>
                <option value="recientes" <?= $orden === 'recientes'  ? 'selected' : '' ?>>Creación reciente</option>
            </select>
        </div>
        <div class="col-sm-1 d-grid">
            <button class="btn btn-primary">Filtrar</button>
        </div>
    </form>

    <!-- Contador -->
    <?php $m = $data['meta'] ?? ['page' => 1, 'per' => 9, 'total' => 0, 'total_pages' => 1];
    $ini = ($m['total'] > 0) ? (($m['page'] - 1) * $m['per'] + 1) : 0;
    $fin = min($m['page'] * $m['per'], $m['total']); ?>
    <div id="evt-counter" class="small text-secondary mb-2">
        <?= $m['total'] > 0 ? "Mostrando {$ini}–{$fin} de {$m['total']} eventos" : 'No hay eventos con ese filtro' ?>
    </div>

    <!-- Listado Masonry -->
    <div id="evt-list">
        <?php $evtItems = $data['items'] ?? [];
        require BASE_PATH . 'Views/Eventos/_cards.php'; ?>
    </div>

    <!-- Paginación -->
    <div id="evt-pag">
        <?php require BASE_PATH . 'Views/Eventos/_paginacion.php'; ?>
    </div>
</main>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>