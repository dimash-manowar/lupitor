<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 text-light mb-0">Ranking — Temporada <?= htmlspecialchars($temporada ?? date('Y')) ?></h1>
        <form method="get" action="<?= BASE_URL ?>Ranking/index" class="d-flex gap-2">
            <input name="temporada" value="<?= htmlspecialchars($temporada ?? date('Y')) ?>" class="form-control form-control-sm bg-dark text-light border-secondary" style="width:120px" placeholder="2025">
            <button class="btn btn-outline-light btn-sm">Cambiar</button>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Jugador</th>
                    <th>Club</th>
                    <th>ELO</th>
                    <th>Puntos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $r): ?>
                    <tr>
                        <td><?= (int)$r['posicion'] ?></td>
                        <td><?= htmlspecialchars($r['jugador']) ?></td>
                        <td><?= htmlspecialchars($r['club']) ?></td>
                        <td><?= (int)$r['elo'] ?></td>
                        <td><?= number_format((float)$r['puntos'], 2, ',', '.') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>