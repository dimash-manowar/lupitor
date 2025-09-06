<?php require BASE_PATH . 'Views/Templates/header.php'; ?>

<main class="container py-5">
    <h1 class="mb-4"><?= $data['titulo'] ?? 'Menú' ?></h1>

    <table class="table table-dark table-striped table-hover">
        <thead>
            <tr>
                <th>Orden</th>
                <th>Título</th>
                <th>Destino</th>
                <th>URL</th>
                <th>Icono</th>
                <th>Visible</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['items'] as $item): ?>
                <tr>
                    <td><?= $item['orden'] ?></td>
                    <td><?= htmlspecialchars($item['titulo']) ?></td>
                    <td><?= $item['destino'] ?></td>
                    <td><?= $item['url'] ?></td>
                    <td><i class="<?= $item['icono'] ?>"></i> <?= $item['icono'] ?></td>
                    <td><?= $item['visible'] ? 'Sí' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</main>

<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>
