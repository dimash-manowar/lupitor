<?php require BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<?php $f = $f ?? []; ?>
<meta name="csrf" content="<?= htmlspecialchars($csrf ?? '') ?>">

<?php
// Construcción de URLs para filtrar por estado sin perder el resto de filtros
$qs = $_GET ?? [];
unset($qs['pagina']); // por si usas /index/{pagina}
$makeUrl = function ($estado) use ($qs) {
    $qsc = $qs;
    if ($estado) $qsc['estado'] = $estado;
    else unset($qsc['estado']);
    return BASE_URL . 'AdminInscripciones/index?' . http_build_query($qsc);
};
$stats = $stats ?? ['pendiente' => 0, 'confirmada' => 0, 'anulada' => 0];
$map = [
    'pendiente'  => ['label' => 'Pendiente', 'class' => 'warning'],
    'confirmada' => ['label' => 'Confirmada', 'class' => 'success'],
    'anulada'    => ['label' => 'Anulada', 'class' => 'danger'],
];
?>
<div class="container-fluid py-3">
    <div class="d-flex align-items-center mb-3 gap-2">
        <h1 class="h4 m-0">Inscripciones</h1>
        <a class="btn btn-outline-success btn-sm ms-auto" href="<?= BASE_URL ?>AdminInscripciones/export?<?= http_build_query($f) ?>">
            <i class="bi bi-download"></i> Exportar CSV
        </a>
        <a class="btn btn-outline-danger btn-sm" href="<?= BASE_URL ?>AdminInscripciones/exportPdf?<?= http_build_query($f) ?>">
            <i class="bi bi-file-pdf"></i> Exportar PDF
        </a>
        <a class="btn btn-outline-light btn-sm" href="<?= BASE_URL ?>AdminCheckin/panel/<?= (int)($f['torneo_id'] ?? 0) ?>" <?= empty($f['torneo_id']) ? 'disabled' : '' ?>>
            <i class="bi bi-qr-code-scan"></i> Check-in
        </a>
    </div>
    <div class="card mb-3 border-secondary-subtle" data-bs-theme="dark">
        <div class="card-body py-3">
            <form class="card card-body mb-3" method="get" action="">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Torneo</label>
                        <select name="torneo_id" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach ($torneos as $t): ?>
                                <option value="<?= (int)$t['id'] ?>" <?= (isset($f['torneo_id']) && (int)$f['torneo_id'] === (int)$t['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($t['titulo']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach (['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'anulada' => 'Anulada'] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= (($f['estado'] ?? '') === $k) ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Pago</label>
                        <select name="pago" class="form-select">
                            <option value="">Todos</option>
                            <option value="ok" <?= (($f['pago'] ?? '') === 'ok') ? 'selected' : '' ?>>Pagado</option>
                            <option value="pend" <?= (($f['pago'] ?? '') === 'pend') ? 'selected' : '' ?>>Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" value="<?= htmlspecialchars($f['desde'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" value="<?= htmlspecialchars($f['hasta'] ?? '') ?>" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Buscar</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($f['q'] ?? '') ?>" class="form-control" placeholder="Nombre, email o ref pago">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Orden</label>
                        <select name="orden" class="form-select">
                            <?php foreach (['recientes' => 'Más recientes', 'fecha_asc' => 'Más antiguas', 'fecha_desc' => 'Más recientes', 'nombre_asc' => 'Nombre A-Z', 'nombre_desc' => 'Nombre Z-A'] as $k => $v): ?>
                                <option value="<?= $k ?>" <?= (($f['orden'] ?? '') === $k) ? 'selected' : '' ?>><?= $v ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Por página</label>
                        <select name="per" class="form-select">
                            <?php foreach ([10, 20, 30, 50] as $n): ?>
                                <option value="<?= $n ?>" <?= ((int)($f['per'] ?? 20) === $n) ? 'selected' : '' ?>><?= $n ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-8 text-end">
                        <button class="btn btn-primary"><i class="bi bi-filter"></i> Filtrar</button>
                        <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>AdminInscripciones/index">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a href="<?= $makeUrl('') ?>" class="btn btn-sm btn-outline-light">
            <i class="bi bi-collection"></i> Todas
            <span class="badge text-bg-secondary ms-1"><?= (int)(($total ?? 0)) ?></span>
        </a>
        <?php foreach ($map as $k => $cfg): ?>
            <a href="<?= $makeUrl($k) ?>"
                class="btn btn-sm btn-outline-<?= $cfg['class'] ?> <?= (($f['estado'] ?? '') === $k) ? 'active' : '' ?>">
                <?= $cfg['label'] ?>
                <span class="badge text-bg-<?= $cfg['class'] ?> ms-1"><?= (int)($stats[$k] ?? 0) ?></span>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="card">
        <div class="table-responsive">
            <?php $COLS = 10; ?>
            <table class="table table-dark table-hover align-middle" style="table-layout:auto;">
                <thead>
                    <tr>
                        <th class="text-muted">ID</th>
                        <th>Torneo</th>
                        <th>Jugador</th>
                        <th>Contacto</th>
                        <th class="text-center">ELO</th>
                        <th class="text-center">Federado</th>
                        <th>Pago</th>
                        <th>Estado</th>
                        <th>Alta</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody class="align-middle">
                    <?php foreach (($items ?? []) as $r): ?>
                        <!-- Fila “tirita” opcional (pago/ref). OJO: colspan = TOTAL de columnas -->
                        <tr>
                            <td colspan="<?= $COLS ?>" class="py-1">
                                <?php if (!empty($r['pago_modo'])): ?>
                                    <?php if ((int)($r['pago_ok'] ?? 0) === 1): ?>
                                        <span class="badge text-bg-success me-2">Pagado</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-warning text-dark me-2">Pend.</span>
                                    <?php endif; ?>
                                    <span class="small text-secondary">
                                        <?= htmlspecialchars($r['pago_modo']) ?>
                                        <?php if (!empty($r['pago_ref'])): ?> - <?= htmlspecialchars($r['pago_ref']) ?><?php endif; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Fila de datos: EXACTAMENTE 10 <td> para alinear con el thead -->
                        <tr data-id="<?= (int)$r['id'] ?>">
                            <td>#<?= (int)$r['id'] ?></td>

                            <td><?= htmlspecialchars($r['torneo'] ?? '') ?></td>

                            <td><?= htmlspecialchars(trim(($r['nombre'] ?? '') . ' ' . ($r['apellidos'] ?? ''))) ?></td>

                            <td>
                                <div><i class="bi bi-envelope me-1"></i><?= htmlspecialchars($r['email'] ?? '') ?></div>
                                <?php if (!empty($r['telefono'])): ?>
                                    <div class="text-secondary"><i class="bi bi-telephone me-1"></i><?= htmlspecialchars($r['telefono']) ?></div>
                                <?php endif; ?>
                            </td>

                            <td class="text-center"><?= $r['elo'] ?: '-' ?></td>

                            <td class="text-center">
                                <?php if ((int)($f['federado'] ?? 0) === 1): ?>
                                    <span class="badge text-bg-success">Sí</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary">No</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ((int)($r['pago_ok'] ?? 0) === 1): ?>
                                    <span class="badge text-bg-success">OK</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning text-dark">Pend.</span>
                                <?php endif; ?>
                                <div class="small text-secondary">
                                    <?= htmlspecialchars($r['pago_modo'] ?? '') ?>
                                    <?php if (!empty($r['pago_ref'])): ?> · <?= htmlspecialchars($r['pago_ref']) ?><?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <?php
                                $estadoMap = ['pendiente' => 'Pendiente', 'confirmada' => 'Confirmada', 'anulada' => 'Anulada'];
                                $estadoTxt = $estadoMap[strtolower((string)($r['estado'] ?? ''))] ?? ($r['estado'] ?? '');
                                ?>
                                <?= htmlspecialchars($estadoTxt) ?>
                            </td>

                            <td><?= isset($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '' ?></td>

                            <?php $ID = (int)$r['id']; ?>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-success js-pago" data-id="<?= $ID ?>" data-ok="<?= (int)!($r['pago_ok'] ?? 0) ?>" title="Alternar pagado">€</button>

                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary js-estado" data-id="<?= $ID ?>" data-estado="confirmada" title="Confirmar">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary js-estado" data-id="<?= $ID ?>" data-estado="pendiente" title="Marcar pendiente">
                                        <i class="bi bi-clock"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning js-estado" data-id="<?= $ID ?>" data-estado="anulada" title="Anular">
                                        <i class="bi bi-slash-circle"></i>
                                    </button>
                                </div>

                                <button class="btn btn-sm btn-outline-light js-mail" data-id="<?= $ID ?>" title="Reenviar email">
                                    <i class="bi bi-envelope"></i>
                                </button>

                                <a class="btn btn-sm btn-outline-info" target="_blank" href="<?= BASE_URL ?>AdminInscripciones/recibo/<?= $ID ?>" title="Recibo PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>

                                <button class="btn btn-sm btn-outline-danger js-del" data-id="<?= $ID ?>" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php
        $pages = (int)($total_pages ?? 1);
        $page  = (int)($page ?? 1);
        $per   = (int)($per ?? 20);
        $qs = $_GET;
        unset($qs['pagina']);
        $url = function ($n, $qs) {
            return BASE_URL . 'AdminInscripciones/index/' . $n . '?' . http_build_query($qs);
        };
        ?>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <small class="text-secondary">Mostrando <?= (int)count($items ?? []) ?> de <?= (int)($total ?? 0) ?> inscripciones</small>
            <nav>
                <ul class="pagination m-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $url(max(1, $page - 1), $qs) ?>">&laquo;</a>
                    </li>
                    <?php for ($p = 1; $p <= $pages; $p++): ?>
                        <li class="page-item <?= $p === $page ? 'active' : '' ?>"><a class="page-link" href="<?= $url($p, $qs) ?>"><?= $p ?></a></li>
                    <?php endfor; ?>
                    <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $url(min($pages, $page + 1), $qs) ?>">&raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
    (function() {
        const csrfEl = document.querySelector('meta[name="csrf"]');
        const csrf = csrfEl ? csrfEl.content : '';

        function post(url, data) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            });
        }

        // Cambiar estado
        document.querySelectorAll('.js-estado').forEach(btn => {
            btn.addEventListener('click', async () => {
                const tr = btn.closest('tr');
                const id = tr?.dataset.id;
                if (!id) return;
                const estado = btn.dataset.estado;
                try {
                    const res = await fetch('<?= BASE_URL ?>AdminInscripciones/cambiarEstado', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            id,
                            estado,
                            csrf
                        })
                    });
                    const j = await res.json();
                    if (!j.ok) throw 0;
                    location.reload();
                } catch (e) {
                    if (window.Swal) Swal.fire({
                        icon: 'error',
                        title: 'No se pudo actualizar'
                    });
                    else alert('No se pudo actualizar');
                }
            });
        });

        // Eliminar
        document.querySelectorAll('.js-del').forEach(btn => {
            btn.addEventListener('click', async () => {
                const tr = btn.closest('tr');
                const id = tr?.dataset.id;
                if (!id) return;

                const go = async () => {
                    try {
                        const res = await fetch('<?= BASE_URL ?>AdminInscripciones/eliminar', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                id,
                                csrf
                            })
                        });
                        const j = await res.json();
                        if (!j.ok) throw 0;
                        tr.remove();
                    } catch (e) {
                        if (window.Swal) Swal.fire({
                            icon: 'error',
                            title: 'No se pudo eliminar'
                        });
                        else alert('No se pudo eliminar');
                    }
                };

                if (window.Swal) {
                    const r = await Swal.fire({
                        icon: 'warning',
                        title: 'Eliminar inscripción',
                        text: 'Esta acción no se puede deshacer',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, eliminar'
                    });
                    if (r.isConfirmed) go();
                } else {
                    if (confirm('¿Eliminar inscripción?')) go();
                }
            });
        });

        // Toggle pago OK
        document.querySelectorAll('.js-pago').forEach(btn => {
            btn.addEventListener('click', async () => {
                const tr = btn.closest('tr');
                const id = tr?.dataset.id;
                if (!id) return;
                const ok = btn.dataset.ok === '1' ? 1 : 0;
                try {
                    const res = await fetch('<?= BASE_URL ?>AdminInscripciones/cambiarPago', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            id,
                            ok,
                            csrf
                        })
                    });
                    const j = await res.json();
                    if (!j.ok) throw 0;
                    location.reload();
                } catch (e) {
                    if (window.Swal) Swal.fire({
                        icon: 'error',
                        title: 'No se pudo actualizar el pago'
                    });
                    else alert('No se pudo actualizar el pago');
                }
            });
        }); // ← esta línea faltaba
        // Reenviar email
        document.querySelectorAll('.js-mail').forEach(btn => {
            btn.addEventListener('click', async () => {
                const tr = btn.closest('tr');
                const id = tr?.dataset.id || btn.closest('[data-id]')?.dataset.id;
                if (!id) return;
                try {
                    btn.disabled = true;
                    const res = await fetch('<?= BASE_URL ?>AdminInscripciones/reenviar', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            id,
                            csrf: '<?= htmlspecialchars($csrf ?? "") ?>'
                        })
                    });
                    const j = await res.json();
                    if (!j.ok) throw 0;
                    if (window.Swal) Swal.fire({
                        icon: 'success',
                        title: 'Email reenviado',
                        timer: 1600,
                        showConfirmButton: false
                    });
                    else alert('Email reenviado');
                } catch (e) {
                    if (window.Swal) Swal.fire({
                        icon: 'error',
                        title: 'No se pudo reenviar'
                    });
                    else alert('No se pudo reenviar');
                } finally {
                    btn.disabled = false;
                }
            });
        });

        // Check-in manual
        document.querySelectorAll('.js-checkin').forEach(btn => {
            btn.addEventListener('click', async () => {
                const tr = btn.closest('tr');
                const id = tr?.dataset.id || btn.closest('[data-id]')?.dataset.id;
                if (!id) return;
                try {
                    btn.disabled = true;
                    const res = await fetch('<?= BASE_URL ?>AdminInscripciones/checkin', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            id,
                            csrf: '<?= htmlspecialchars($csrf ?? "") ?>'
                        })
                    });
                    const j = await res.json();
                    if (!j.ok) throw 0;
                    if (window.Swal) Swal.fire({
                        icon: 'success',
                        title: j.repeat ? 'Ya tenía check-in' : 'Check-in OK',
                        timer: 1400,
                        showConfirmButton: false
                    });
                    else alert(j.repeat ? 'Ya tenía check-in' : 'Check-in OK');
                    location.reload();
                } catch (e) {
                    if (window.Swal) Swal.fire({
                        icon: 'error',
                        title: 'No se pudo hacer check-in'
                    });
                    else alert('No se pudo hacer check-in');
                } finally {
                    btn.disabled = false;
                }
            });
        });

    })(); // fin IIFE
</script>

<?php require BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>