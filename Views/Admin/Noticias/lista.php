<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="container-fluid">
    <div class="row">
        
        <main class="container py-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h4 m-0">Noticias — Admin</h1>
                <a class="btn btn-primary" href="<?= BASE_URL ?>AdminNoticias/crear"><i class="bi bi-plus"></i> Nueva</a>
            </div>


            <form class="row g-2 mb-2" method="get" action="">
                <div class="col-sm-3">
                    <?php $e = $data['f_estado'] ?? ''; ?>
                    <select name="estado" class="form-select">
                        <option value="">Todos los estados</option>
                        <option value="borrador" <?= $e === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                        <option value="publicado" <?= $e === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                    </select>
                </div>
                <div class="col-sm-3">
                    <?php $c = $data['f_categoria'] ?? ''; ?>
                    <select name="categoria" class="form-select">
                        <option value="">Todas las categorías</option>
                        <?php foreach (['club', 'ajedrez', 'escuela'] as $cc): ?>
                            <option value="<?= $cc ?>" <?= $c === $cc ? 'selected' : '' ?>><?= ucfirst($cc) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-sm-4"><input type="search" name="q" class="form-control" placeholder="Buscar…" value="<?= htmlspecialchars($data['q'] ?? '') ?>"></div>
                <div class="col-sm-2 d-grid"><button class="btn btn-outline-light">Filtrar</button></div>
            </form>


            <?php if (!empty($_SESSION['flash_ok'])): ?>
                <div class="alert alert-success"><?= $_SESSION['flash_ok'];
                                                    unset($_SESSION['flash_ok']); ?></div>
            <?php endif; ?>


            <div class="table-responsive">
                <table class="table table-dark table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Portada</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Publicación</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($data['items'] ?? []) as $n): ?>
                            <tr>
                                <td style="width:76px;">
                                    <?php if (!empty($n['portada'])): ?>
                                        <img src="<?= BASE_URL . $n['portada'] ?>" class="rounded" width="64" height="42" style="object-fit:cover" alt="">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($n['titulo']) ?></div>
                                    <div class="text-secondary small">/noticias/<?= htmlspecialchars($n['slug']) ?></div>
                                </td>
                                <td><?= htmlspecialchars(ucfirst($n['categoria'])) ?></td>
                                <td>
                                    <?php if ($n['estado'] === 'publicado'): ?>
                                        <span class="badge bg-success">Publicado</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Borrador</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $n['publicado_at'] ? date('d/m/Y H:i', strtotime($n['publicado_at'])) : '-' ?></td>
                                <td class="text-end">
                                    <form action="<?= BASE_URL ?>AdminNoticias/publicar/<?= (int)$n['id'] ?>" method="post" class="d-inline">
                                        <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>">
                                        <button class="btn btn-sm <?= $n['estado'] === 'publicado' ? 'btn-warning' : 'btn-success' ?>">
                                            <?= $n['estado'] === 'publicado' ? 'Despublicar' : 'Publicar' ?>
                                        </button>
                                    </form>
                                    <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL ?>AdminNoticias/editar/<?= (int)$n['id'] ?>">Editar</a>
                                    <form action="<?= BASE_URL ?>AdminNoticias/borrar/<?= (int)$n['id'] ?>" method="post" class="d-inline" onsubmit="return confirm('¿Borrar noticia?');">
                                        <input type="hidden" name="csrf" value="<?= $data['csrf'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Borrar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<script>
    document.querySelectorAll('form[action*="AdminNoticias/borrar/"]').forEach(f => {
        f.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Borrar noticia?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, borrar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then(r => {
                if (r.isConfirmed) {
                    f.onsubmit = null; // evita el confirm() inline si lo hubiera
                    f.submit();
                }
            });
        });
    });
</script>


<?php require BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>