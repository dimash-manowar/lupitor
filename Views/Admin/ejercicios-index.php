<?php require_once BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>


<form class="row g-2 mb-3" method="get">
    <div class="col-sm-4">
        <select name="nivel" class="form-select">
            <?php $niv = $data['nivel'] ?? ''; ?>
            <option value="">Todos los niveles</option>
            <?php foreach (['Iniciación', 'Intermedio', 'Avanzado'] as $n): ?>
                <option value="<?= $n ?>" <?= $niv === $n ? 'selected' : '' ?>><?= $n ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-sm-3">
        <select name="pub" class="form-select">
            <?php $pub = $data['pub']; ?>
            <option value="">Todos</option>
            <option value="1" <?= (string)$pub === '1' ? 'selected' : '' ?>>Públicos</option>
            <option value="0" <?= (string)$pub === '0' ? 'selected' : '' ?>>Privados</option>
        </select>
    </div>

    <div class="col-sm-2"><button class="btn btn-outline-light w-100">Filtrar</button></div>
    <div class="col-sm-3 text-end"><a class="btn btn-success" href="<?= BASE_URL ?>admin/ejerciciosCrear"><i class="bi bi-plus-circle"></i> Nuevo</a></div>
</form>


<div class="table-responsive">
    <table class="table table-dark table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Título</th>
                <th>Nivel</th>
                <th>Visibilidad</th>
                <th>Creado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($data['items'] ?? []) as $e): ?>
                <tr>
                    <td><?= (int)$e['id'] ?></td>
                    <td><?= htmlspecialchars($e['titulo']) ?></td>
                    <td><?= htmlspecialchars($e['nivel']) ?></td>
                    <td>
                        <?php if ((int)$e['es_publico'] === 1): ?>
                            <span class="badge bg-success">Público</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Privado</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($e['created_at'] ?? '') ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-warning" href="<?= BASE_URL ?>Admin/ejerciciosEditar/<?= (int)$e['id'] ?>">Editar</a>
                        <a class="btn btn-sm btn-info" href="<?= BASE_URL ?>AdminEjAsignaciones/asignar/<?= (int)$e['id'] ?>">Asignar</a>
                        <form class="d-inline" action="<?= BASE_URL ?>admin/ejerciciosEliminar/<?= (int)$e['id'] ?>" method="post">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($data['csrf']) ?>">
                            <button class="btn btn-sm btn-danger js-confirm" type="submit" data-title="Eliminar ejercicio" data-text="Esta acción no se puede deshacer.">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($data['items'])): ?>
                <tr>
                    <td colspan="6" class="text-center text-secondary p-4">No hay ejercicios</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<?php $m = $data['meta'] ?? ['page' => 1, 'total_pages' => 1];
$base = BASE_URL . 'admin/ejercicios/'; ?>
<nav class="mt-3">
    <ul class="pagination">
        <li class="page-item <?= $m['page'] <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= $base . max(1, $m['page'] - 1) ?>">Anterior</a></li>
        <?php for ($p = 1; $p <= ($m['total_pages'] ?? 1); $p++): ?>
            <li class="page-item <?= $p == $m['page'] ? 'active' : '' ?>"><a class="page-link" href="<?= $base . $p ?>"><?= $p ?></a></li>
        <?php endfor; ?>
        <li class="page-item <?= $m['page'] >= ($m['total_pages'] ?? 1) ? 'disabled' : '' ?>"><a class="page-link" href="<?= $base . min(($m['total_pages'] ?? 1), $m['page'] + 1) ?>">Siguiente</a></li>

    </ul>
</nav>

<?php require_once BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>