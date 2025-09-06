
<?php
$isAlumnos = in_array(($a['slug'] ?? ''), ['alumnos', 'alumnos-del-club'], true);
?>
<h1 class="h4 text-light mb-3"><?= htmlspecialchars($a['titulo']) ?></h1>

<?php if ($isAlumnos): ?>
    <!-- ====== SECCIÓN FOTOS (alumnos) ====== -->
    <h2 class="h6 text-secondary mt-2 mb-2"><i class="bi bi-images me-1"></i> Fotos del alumnado</h2>
    <div class="row g-2 mb-3">
        <?php foreach (($fotos ?? []) as $m): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="media-card">
                    <div class="media-thumb">
                        <?php if (!empty($m['archivo_path'])): ?>
                            <img src="<?= BASE_URL . $m['archivo_path'] ?>" alt="<?= htmlspecialchars($m['titulo']) ?>"
                                loading="lazy" data-bs-toggle="modal" data-bs-target="#galModal"
                                data-type="img" data-src="<?= BASE_URL . $m['archivo_path'] ?>" data-title="<?= htmlspecialchars($m['titulo']) ?>">
                        <?php endif; ?>
                    </div>
                    <div class="media-body">
                        <p class="media-title text-truncate" title="<?= htmlspecialchars($m['titulo']) ?>"><?= htmlspecialchars($m['titulo']) ?></p>
                        <div class="d-flex justify-content-between">
                            <span class="media-meta"><?= !empty($m['album']) ? htmlspecialchars($m['album']) : 'Alumnos' ?></span>
                            <span class="chip"><i class="bi bi-image"></i> foto</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
        if (empty($fotos)): ?>
            <div class="text-secondary">Aún no hay fotos.</div>
        <?php endif; ?>
    </div>

    <!-- ====== SECCIÓN VÍDEOS (partidas) ====== -->
    <h2 class="h6 text-secondary mt-3 mb-2"><i class="bi bi-camera-video me-1"></i> Vídeos de partidas</h2>
    <div class="row g-2">
        <?php foreach (($videos ?? []) as $m): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="media-card">
                    <div class="media-thumb">
                        <?php
                        $src = '';
                        if (!empty($m['youtube_id']))      $src = 'https://www.youtube.com/embed/' . htmlspecialchars($m['youtube_id']) . '?autoplay=1';
                        elseif (!empty($m['video_path'])) $src = BASE_URL . $m['video_path'];
                        ?>
                        <div class="d-flex align-items-center justify-content-center text-secondary"
                            style="width:100%;height:100%;cursor:pointer"
                            data-bs-toggle="modal" data-bs-target="#galModal"
                            data-type="video" data-src="<?= $src ?>" data-title="<?= htmlspecialchars($m['titulo']) ?>">
                            <i class="bi bi-play-circle fs-1"></i>
                        </div>
                    </div>
                    <div class="media-body">
                        <p class="media-title text-truncate" title="<?= htmlspecialchars($m['titulo']) ?>"><?= htmlspecialchars($m['titulo']) ?></p>
                        <?php if (!empty($m['alumno_nombre'])): ?>
                            <div class="media-meta">Alumno: <?= htmlspecialchars($m['alumno_nombre']) ?></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between">
                            <span class="media-meta"><?= !empty($m['album']) ? htmlspecialchars($m['album']) : 'Alumnos' ?></span>
                            <span class="chip"><i class="bi bi-camera-video"></i> vídeo</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
        if (empty($videos)): ?>
            <div class="text-secondary">Aún no hay vídeos.</div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- ÁLBUM NORMAL (grid único) -->
    <div class="row g-2">
        <?php foreach (($items ?? []) as $m): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="media-card">
                    <div class="media-thumb">
                        <?php if (($m['tipo'] ?? 'imagen') === 'imagen' && !empty($m['archivo_path'])): ?>
                            <img src="<?= BASE_URL . $m['archivo_path'] ?>" alt="<?= htmlspecialchars($m['titulo']) ?>"
                                loading="lazy" data-bs-toggle="modal" data-bs-target="#galModal"
                                data-type="img" data-src="<?= BASE_URL . $m['archivo_path'] ?>" data-title="<?= htmlspecialchars($m['titulo']) ?>">
                        <?php else: ?>
                            <?php
                            $src = '';
                            if (!empty($m['youtube_id']))      $src = 'https://www.youtube.com/embed/' . htmlspecialchars($m['youtube_id']) . '?autoplay=1';
                            elseif (!empty($m['video_path'])) $src = BASE_URL . $m['video_path'];
                            ?>
                            <div class="d-flex align-items-center justify-content-center text-secondary"
                                style="width:100%;height:100%;cursor:pointer"
                                data-bs-toggle="modal" data-bs-target="#galModal"
                                data-type="video" data-src="<?= $src ?>" data-title="<?= htmlspecialchars($m['titulo']) ?>">
                                <i class="bi bi-play-circle fs-1"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="media-body">
                        <p class="media-title text-truncate" title="<?= htmlspecialchars($m['titulo']) ?>"><?= htmlspecialchars($m['titulo']) ?></p>
                        <?php if (!empty($m['alumno_nombre'])): ?>
                            <div class="media-meta">Alumno: <?= htmlspecialchars($m['alumno_nombre']) ?></div>
                        <?php endif; ?>
                        <div class="d-flex justify-content-between">
                            <span class="media-meta"><?= !empty($m['album']) ? htmlspecialchars($m['album']) : '' ?></span>
                            <?php if (($m['tipo'] ?? '') === 'video'): ?>
                                <span class="chip"><i class="bi bi-camera-video"></i> vídeo</span>
                            <?php else: ?>
                                <span class="chip"><i class="bi bi-image"></i> foto</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
        if (empty($items)): ?>
            <div class="text-secondary">No hay contenido disponible.</div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<!-- Modal lightbox (reutilizado) -->
<div class="modal fade" id="galModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-black">
            <div class="modal-header border-0">
                <h5 class="modal-title text-light" id="galTitle"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-0" id="galBody"></div>
        </div>
    </div>
</div>

<script>
    (function() {
        const modal = document.getElementById('galModal');
        const body = document.getElementById('galBody');
        const title = document.getElementById('galTitle');

        modal.addEventListener('show.bs.modal', e => {
            const t = e.relatedTarget;
            const type = t?.dataset.type;
            const src = t?.dataset.src || '';
            title.textContent = t?.dataset.title || '';
            if (type === 'img') {
                body.innerHTML = `<img src="${src}" alt="" style="width:100%;height:auto;">`;
            } else if (type === 'video') {
                if (src.includes('youtube.com')) {
                    body.innerHTML = `<div class="ratio ratio-16x9"><iframe src="${src}" title="YouTube" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>`;
                } else if (src) {
                    body.innerHTML = `<video src="${src}" controls autoplay style="width:100%;height:auto;"></video>`;
                } else {
                    body.innerHTML = '<div class="text-center text-secondary py-5">Vídeo no disponible</div>';
                }
            }
        });
        modal.addEventListener('hidden.bs.modal', () => {
            body.innerHTML = '';
        });
    })();
</script>
