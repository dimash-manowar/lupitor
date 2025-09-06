<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 text-light mb-0">Eventos</h1>
    <a href="<?= BASE_URL ?>AdminEventos/crear" class="btn btn-warning">+ Nuevo evento</a>
</div>
<div class="table-responsive">
    <table class="table table-dark align-middle">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Título</th>
                <th>Lugar</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th></th>
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
                    <td class="text-end">
                        <a href="<?= BASE_URL ?>AdminEventos/editar/<?= (int)$e['id'] ?>" class="btn btn-sm btn-outline-light">Editar</a>
                        <form action="<?= BASE_URL ?>AdminEventos/borrar/<?= (int)$e['id'] ?>" method="post" class="d-inline js-del">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">
                            <button class="btn btn-sm btn-outline-danger">Borrar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    document.querySelectorAll('.js-del').forEach(f => {
        f.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Borrar evento?',
                text: 'No se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, borrar',
                background: '#151a21',
                color: '#e6eaf2'
            }).then(r => {
                if (r.isConfirmed) this.submit();
            });
        });
    });
</script>
<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>