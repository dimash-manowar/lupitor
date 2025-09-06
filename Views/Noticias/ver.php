<?php
$data['hideNavbar'] = true;   // oculta la barra
$data['showHero']   = false;  // (opcional, por defecto ya está en false)
?>
<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<main class="container py-5">
    <?php $n = $data['n'];
    $gal = $data['galeria'] ?? []; ?>
    <article class="mx-auto" style="max-width: 860px;">
        <nav class="mb-3 d-flex gap-2">
            <a href="<?= BASE_URL ?>" class="btn btn-sm btn-outline-light">
                <i class="bi bi-house-door"></i> Inicio
            </a>
            <a href="<?= BASE_URL ?>Noticias" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-newspaper"></i> Noticias
            </a>
        </nav>
        <header class="mb-3 text-center">
            <div class="mb-2"><span class="badge bg-primary"><?= htmlspecialchars(ucfirst($n['categoria'])) ?></span></div>
            <h1 class="display-6 mb-2"><?= htmlspecialchars($n['titulo']) ?></h1>
            <?php if (!empty($n['publicado_at'])): ?><div class="text-secondary small"><?= date('d/m/Y H:i', strtotime($n['publicado_at'])) ?></div><?php endif; ?>
        </header>


        <?php if (!empty($n['portada'])): ?>
            <img src="<?= BASE_URL . $n['portada'] ?>" class="img-fluid rounded mb-4" alt="<?= htmlspecialchars($n['titulo']) ?>">
        <?php endif; ?>


        <div class="contenido lead">
            <?= $n['contenido'] /* contenido redactado por admin */ ?>
        </div>

        <?php if (!empty($galeria)): ?>
            <section class="mt-5" id="galeria">
                <h3 class="h5 mb-3">Galería</h3>
                <div class="masonry">
                    <?php foreach ($galeria as $m): $src = BASE_URL . $m['path'];
                        $mime = $m['mime']; ?>
                        <a class="mitem d-block" href="<?= $src ?>" data-media-src="<?= $src ?>" data-media-type="<?= str_starts_with($mime, 'video/') ? 'video' : (str_starts_with($mime, 'audio/') ? 'audio' : 'image') ?>">
                            <?php if (str_starts_with($mime, 'image/')): ?>
                                <img src="<?= $src ?>" class="w-100 rounded" style="display:block; object-fit:cover;">
                            <?php elseif (str_starts_with($mime, 'video/')): ?>
                                <div class="ratio ratio-16x9 rounded bg-dark">
                                    <span class="text-white d-flex justify-content-center align-items-center">▶ Video</span>
                                </div>
                            <?php else: ?>
                                <div class="rounded border p-2 text-white-50">🎧 Audio</div>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>

        <?php endif; ?>
    </article>
    <div class="modal fade" id="mediaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg"></i> Cerrar
                    </button>
                </div>
                <div class="modal-body p-0 d-flex align-items-center justify-content-center">
                    <img id="mmImg" class="img-fluid d-none" alt="">
                    <video id="mmVideo" class="w-100 d-none" controls preload="metadata"></video>
                    <audio id="mmAudio" class="w-100 d-none" controls></audio>
                </div>
            </div>
        </div>
    </div>
</main>
<script>
    (() => {
        const modalEl = document.getElementById('mediaModal');
        if (!modalEl) return;
        const bsModal = new bootstrap.Modal(modalEl);
        const img = modalEl.querySelector('#mmImg');
        const vid = modalEl.querySelector('#mmVideo');
        const aud = modalEl.querySelector('#mmAudio');

        function show(type, src) {
            img.classList.add('d-none');
            vid.classList.add('d-none');
            aud.classList.add('d-none');
            if (type === 'video') {
                vid.src = src;
                vid.classList.remove('d-none');
                vid.load();
            } else if (type === 'audio') {
                aud.src = src;
                aud.classList.remove('d-none');
                aud.load();
            } else {
                img.src = src;
                img.classList.remove('d-none');
            }
            bsModal.show();
        }

        document.addEventListener('click', (e) => {
            const a = e.target.closest('[data-media-src]');
            if (!a) return;
            e.preventDefault();
            show(a.dataset.mediaType || 'image', a.dataset.mediaSrc);
        });

        modalEl.addEventListener('hidden.bs.modal', () => {
            // limpiar recursos
            vid.pause();
            vid.removeAttribute('src');
            aud.pause();
            aud.removeAttribute('src');
            img.removeAttribute('src');
        });

        function fitMasonry(gridSel) {
            const g = document.querySelector(gridSel);
            if (!g) return;
            const row = parseFloat(getComputedStyle(g).getPropertyValue('grid-auto-rows'));
            const gap = parseFloat(getComputedStyle(g).gap);
            g.querySelectorAll(':scope > *').forEach(el => {
                el.style.gridRowEnd = `span ${Math.ceil((el.getBoundingClientRect().height+gap)/(row+gap))}`;
            });
        }
        window.addEventListener('load', () => fitMasonry('.grid-msn'));
        window.addEventListener('resize', () => fitMasonry('.grid-msn'));
    })();
</script>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>