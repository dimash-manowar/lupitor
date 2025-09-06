<?php
$items = $items ?? [];
$page = $page ?? 1;
$total_pages = $total_pages ?? 1;
?>
<h1 class="h4 text-light mb-3">Mis inscripciones</h1>

<?php if (empty($items)): ?>
    <div class="alert alert-dark border-secondary">Aún no tienes inscripciones.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-dark table-striped align-middle">
            <thead>
                <tr>
                    <th>Torneo</th>
                    <th class="text-nowrap">Fecha</th>
                    <th>Lugar</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $it): ?>
                    <tr>
                        <td><?= htmlspecialchars($it['titulo']) ?></td>
                        <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($it['inicio'])) ?></td>
                        <td><?= htmlspecialchars($it['lugar'] ?? '') ?></td>
                        <td>
                            <?php if ((int)$it['pago_ok'] === 1): ?>
                                <span class="badge text-bg-success">OK</span>
                                <small class="text-secondary d-block"><?= htmlspecialchars($it['pago_modo'] ?? '') ?></small>
                            <?php else: ?>
                                <span class="badge text-bg-warning text-dark">Pendiente</span>
                                <?php if (!empty($it['pago_modo'])): ?>
                                    <small class="text-secondary d-block"><?= htmlspecialchars($it['pago_modo']) ?></small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge text-bg-secondary"><?= htmlspecialchars($it['estado'] ?? '') ?></span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-light" target="_blank"
                                href="<?= BASE_URL . 'UsuarioInscripciones/pdf/' . (int)$it['id'] ?>">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                            <?php if (!empty($it['checkin_token'])): ?>
                                <a class="btn btn-sm btn-outline-info" target="_blank"
                                    href="<?= BASE_URL . 'Inscripcion/qr?token=' . urlencode($it['checkin_token']) ?>">
                                    <i class="bi bi-qr-code"></i>
                                </a>
                            <?php endif; ?>
                            <?php
                            // Enlace ICS rápido (evento 2h; ajusta si quieres)
                            $start = date('Y-m-d H:i', strtotime($it['inicio']));
                            $end   = date('Y-m-d H:i', strtotime($it['inicio'] . ' +2 hours'));
                            $sum   = rawurlencode($it['titulo']);
                            $loc   = rawurlencode((string)($it['lugar'] ?? ''));
                            $desc  = rawurlencode('Torneo de ajedrez — Club de Ajedrez de Berriozar');
                            $ics   = BASE_URL . 'ics.php?summary=' . $sum . '&location=' . $loc . '&description=' . $desc . '&start=' . $start . '&end=' . $end;
                            ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= $ics ?>">
                                <i class="bi bi-calendar-plus"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_pages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination pagination-sm">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                        <a class="page-link" href="<?= BASE_URL . 'UsuarioInscripciones/index/' . $p ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>

<?php endif; ?>