<?php
$mdlE = new EventoModel();
$proximos = $mdlE->proximos(4);
$temporada = date('Y');
$mdlR = new RankingModel();
$top = $mdlR->top($temporada, 5);
?>
<div class="card bg-dark border-secondary h-100">
    <div class="card-body">
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-warning text-dark me-2">Agenda</span>
            <h3 class="h5 mb-0">Próximas actividades</h3>
        </div>
        <ul class="list-group list-group-flush">
            <?php foreach ($proximos as $e): ?>
                <li class="list-group-item bg-dark text-light d-flex justify-content-between">
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($e['titulo']) ?></div>
                        <small class="text-secondary"><?= date('d/m H:i', strtotime($e['inicio'])) ?> · <?= htmlspecialchars($e['lugar'] ?? '') ?></small>
                    </div>
                    <span class="badge bg-secondary"><?= htmlspecialchars($e['tipo']) ?></span>
                </li>
            <?php endforeach; ?>
            <?php if (empty($proximos)): ?>
                <li class="list-group-item bg-dark text-secondary">Sin eventos próximos</li>
            <?php endif; ?>
        </ul>
        <a href="<?= BASE_URL ?>Agenda/index" class="btn btn-outline-light btn-sm mt-3">Ver agenda</a>


        <hr class="border-secondary my-4">


        <div class="d-flex align-items-center mb-2">
            <span class="badge bg-primary me-2">Ranking <?= $temporada ?></span>
            <h3 class="h6 mb-0">Top 5</h3>
        </div>
        <ol class="list-group list-group-numbered list-group-flush">
            <?php foreach ($top as $r): ?>
                <li class="list-group-item bg-dark text-light d-flex justify-content-between">
                    <span><?= htmlspecialchars($r['jugador']) ?></span>
                    <small class="text-secondary">ELO <?= (int)$r['elo'] ?> · <?= number_format((float)$r['puntos'], 2, ',', '.') ?> pts</small>
                </li>
            <?php endforeach; ?>
            <?php if (empty($top)): ?>
                <li class="list-group-item bg-dark text-secondary">Aún no hay ranking</li>
            <?php endif; ?>
        </ol>
        <a href="<?= BASE_URL ?>Ranking/index/<?= $temporada ?>" class="btn btn-outline-primary btn-sm mt-3">Ver ranking</a>
    </div>
</div>