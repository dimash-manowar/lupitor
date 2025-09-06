<?php
$evs = $data['eventos_home'] ?? [];
if (!is_array($evs)) $evs = [];
?>
<section id="sec-eventos" class="container my-5">
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h2 class="h4 m-0">Próximos eventos</h2>
            <small class="text-secondary">Torneos y actividades del club</small>
        </div>
        <a href="<?= BASE_URL ?>Eventos" class="btn btn-outline-light btn-sm">Ver todos</a>
    </div>

    <?php if (!$evs): ?>
        <div class="text-secondary">No hay eventos próximos publicados.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($evs as $e): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="card bg-dark border-0 shadow-sm h-100">
                        <?php if (!empty($e['portada'])): ?>
                            <div class="ratio ratio-16x9">
                                <img src="<?= BASE_URL . $e['portada'] ?>" alt="<?= htmlspecialchars($e['titulo']) ?>"
                                    class="w-100 h-100" style="object-fit: cover;">
                            </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-center gap-2 small text-secondary mb-2">
                                <i class="bi bi-calendar-event"></i>
                                <?php
                                $fecha = !empty($e['fecha']) ? date('d/m/Y', strtotime($e['fecha'])) : '';
                                $hora  = !empty($e['hora'])  ? substr($e['hora'], 0, 5) : '';
                                ?>
                                <span><?= $fecha ?><?= $hora ? " · $hora" : "" ?></span>
                            </div>

                            <h3 class="h5 mb-2">
                                <a class="link-light text-decoration-none"
                                    href="<?= BASE_URL ?>Eventos/ver/<?= urlencode($e['slug'] ?? (string)$e['id']) ?>">
                                    <?= htmlspecialchars($e['titulo']) ?>
                                </a>
                            </h3>

                            <?php if (!empty($e['lugar'])): ?>
                                <div class="small text-secondary mb-2">
                                    <i class="bi bi-geo-alt"></i> <?= htmlspecialchars($e['lugar']) ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($e['resumen'])): ?>
                                <p class="text-secondary mb-3 line-clamp-3"><?= htmlspecialchars($e['resumen']) ?></p>
                            <?php endif; ?>

                            <div class="mt-auto d-flex flex-wrap gap-2">
                                <a class="btn btn-outline-light btn-sm"
                                    href="<?= BASE_URL ?>Eventos/ver/<?= urlencode($e['slug'] ?? (string)$e['id']) ?>">
                                    Detalles
                                </a>
                                <?php if (!empty($e['form_activo'])): ?>
                                    <a class="btn btn-primary btn-sm"
                                        href="<?= BASE_URL ?>Eventos/inscribirse/<?= (int)$e['id'] ?>">
                                        Inscribirme
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>