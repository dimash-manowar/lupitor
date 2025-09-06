<?php require_once BASE_PATH . 'Views/Templates/header.php'; ?>
<section class="container my-5">
    <h2 class="text-center text-info mb-4"><?= $data['titulo'] ?></h2>


    <form class="mb-3" method="get" action="">
        <div class="input-group">
            <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" class="form-control" placeholder="Buscar noticias...">
            <button class="btn btn-outline-light">Buscar</button>
        </div>
    </form>


    <div class="row g-4">
        <?php foreach (($data['items'] ?? []) as $n): ?>
            <div class="col-md-4">
                <div class="card bg-secondary text-white h-100">
                    <?php if (!empty($n['imagen'])): ?>
                        <img src="<?= BASE_URL . 'Assets/img/' . $n['imagen'] ?>" class="card-img-top" alt="imagen">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title mb-1"><?= htmlspecialchars($n['titulo']) ?></h5>
                        <small class="text-white-50"><?= date('d/m/Y', strtotime($n['fecha'])) ?></small>
                        <p class="card-text mt-2"><?= htmlspecialchars($n['resumen']) ?></p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>


    <?php
    $meta = $data['meta'] ?? ['page' => 1, 'total_pages' => 1];
    $base = BASE_URL . 'noticias/index/';
    ?>
    <nav class="mt-4">
        <ul class="pagination justify-content-center">
            <li class="page-item <?= $meta['page'] <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base . max(1, $meta['page'] - 1) ?>">Anterior</a>
            </li>
            <?php for ($p = 1; $p <= $meta['total_pages']; $p++): ?>
                <li class="page-item <?= $p == $meta['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $base . $p ?>"><?= $p ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $meta['page'] >= $meta['total_pages'] ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= $base . min($meta['total_pages'], $meta['page'] + 1) ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
</section>
<?php require_once BASE_PATH . 'Views/Templates/footer.php'; ?>