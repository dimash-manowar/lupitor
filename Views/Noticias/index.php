<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h1 class="h3 m-0">Noticias</h1>
            <small class="text-secondary">Novedades del club y ajedrez local</small>
        </div>
    </div>

    <!-- Filtros -->
    <form id="news-form" class="row g-2 mb-3" method="get" action="" data-base="<?= BASE_URL ?>Noticias/index/">
        <div class="col-sm-3">
            <?php $cat = $data['categoria'] ?? ''; ?>
            <select name="categoria" class="form-select">
                <option value="">Todas las categorías</option>
                <?php foreach (['club', 'ajedrez', 'escuela'] as $cc): ?>
                    <option value="<?= $cc ?>" <?= $cat === $cc ? 'selected' : '' ?>><?= ucfirst($cc) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6">
            <input type="search" name="q" class="form-control" placeholder="Buscar…" value="<?= htmlspecialchars($data['q'] ?? '') ?>">
        </div>
        <div class="col-sm-2">
            <?php $orden = $data['orden'] ?? 'recientes'; ?>
            <select name="orden" class="form-select">
                <option value="recientes" <?= $orden === 'recientes'   ? 'selected' : '' ?>>Más recientes</option>
                <option value="titulo_asc" <?= $orden === 'titulo_asc'  ? 'selected' : '' ?>>Título (A→Z)</option>
                <option value="titulo_desc" <?= $orden === 'titulo_desc' ? 'selected' : '' ?>>Título (Z→A)</option>
                <option value="pub_asc" <?= $orden === 'pub_asc'     ? 'selected' : '' ?>>Fecha (antiguas)</option>
                <option value="pub_desc" <?= $orden === 'pub_desc'    ? 'selected' : '' ?>>Fecha (recientes)</option>
            </select>
        </div>
        <div class="col-sm-1 d-grid">
            <button class="btn btn-primary">Filtrar</button>
        </div>
        <nav class="mb-3">
            <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-light">
                <i class="bi bi-house-door"></i> Inicio
            </a>
        </nav>
    </form>

    <!-- Contador -->
    <?php $m = $data['meta'] ?? ['page' => 1, 'per' => 9, 'total' => 0, 'total_pages' => 1];
    $ini = ($m['total'] > 0) ? (($m['page'] - 1) * $m['per'] + 1) : 0;
    $fin = min($m['page'] * $m['per'], $m['total']); ?>
    <div id="news-counter" class="small text-secondary mb-2">
        <?= $m['total'] > 0 ? "Mostrando {$ini}–{$fin} de {$m['total']} noticias" : 'No hay noticias con ese filtro' ?>
    </div>

    <!-- Listado -->
    <div id="news-list">
        <?php $newsItems = $data['items'] ?? []; ?>
        <?php require BASE_PATH . 'Views/Noticias/_cards.php'; ?>
    </div>

    <!-- Paginación -->
    <div id="news-pag">
        <?php require BASE_PATH . 'Views/Noticias/_paginacion.php'; ?>
    </div>
</main>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>