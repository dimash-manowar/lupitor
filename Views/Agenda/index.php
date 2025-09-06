<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container py-4">
    <h1 class="h3 text-light mb-3">Agenda</h1>
    <div class="table-responsive">
        <table class="table table-dark align-middle">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Título</th>
                    <th>Lugar</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($rows ?? []) as $e): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($e['inicio'])) ?></td>
                        <td><?= htmlspecialchars($e['titulo']) ?></td>
                        <td><?= htmlspecialchars($e['lugar'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($e['tipo']) ?></span></td>
                        <td><?= htmlspecialchars($e['estado']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>