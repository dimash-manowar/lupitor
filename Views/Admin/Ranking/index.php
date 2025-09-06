<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<h1 class="h4 text-light mb-3">Ranking — Importar</h1>
<form action="<?= BASE_URL ?>AdminRanking/importar" method="post" enctype="multipart/form-data" class="card bg-dark border-0 shadow-sm p-3 mb-4">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
    <div class="row g-3 align-items-end">
        <div class="col-sm-2">
            <label class="form-label text-secondary">Temporada</label>
            <input name="temporada" value="<?= htmlspecialchars($temporada) ?>" class="form-control bg-dark text-light border-secondary" required>
        </div>
        <div class="col-sm-6">
            <label class="form-label text-secondary">CSV (pos;jugador;elo;puntos;club)</label>
            <input type="file" name="csv" accept=".csv" class="form-control bg-dark text-light border-secondary" required>
        </div>
        <div class="col-sm-4">
            <button class="btn btn-primary">Importar</button>
            <button formmethod="post" formaction="<?= BASE_URL ?>AdminRanking/vaciarTemporada" class="btn btn-outline-danger ms-2">Vaciar temporada</button>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <input type="hidden" name="temporada" value="<?= htmlspecialchars($temporada) ?>">
        </div>
    </div>
</form>
<p class="text-secondary">Consejo: exporta el CSV con separador `;` desde tu hoja de cálculo.</p>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>