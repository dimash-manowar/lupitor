<?php require BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<?php $n = $data['n'] ?? [];
$gal = $data['galeria'] ?? []; ?>
<div class="container-fluid">
    <div class="row">
       
        <main class="container py-4" style="max-width: 980px;">
            <h1 class="h4 mb-3"><?= htmlspecialchars($data['titulo'] ?? 'Noticia') ?></h1>


            <form action="<?= isset($n['id']) ? BASE_URL . 'AdminNoticias/actualizar/' . $n['id'] : BASE_URL . 'AdminNoticias/guardar' ?>" method="post" enctype="multipart/form-data" class="row g-3">
                <input type="hidden" name="csrf" value="<?= $data['csrf'] ?? csrfToken() ?>">


                <div class="col-md-8">
                    <label class="form-label">Título</label>
                    <input name="titulo" class="form-control" required value="<?= htmlspecialchars($n['titulo'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Categoría</label>
                    <select name="categoria" class="form-select">
                        <?php foreach (['club', 'ajedrez', 'escuela'] as $cc): ?>
                            <option value="<?= $cc ?>" <?= ($n['categoria'] ?? 'club') === $cc ? 'selected' : '' ?>><?= ucfirst($cc) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>


                <div class="col-12">
                    <label class="form-label">Resumen (opcional)</label>
                    <textarea name="resumen" class="form-control" rows="2" maxlength="500"><?= htmlspecialchars($n['resumen'] ?? '') ?></textarea>
                </div>


                <div class="col-12">
                    <label class="form-label">Contenido (HTML permitido)</label>
                    <textarea name="contenido" class="form-control" rows="10"><?= htmlspecialchars($n['contenido'] ?? '') ?></textarea>
                    <div class="form-text">Puedes pegar HTML básico (p, h2, ul/li, img, iframe…).</div>
                </div>


                <div class="col-md-6">
                    <label class="form-label">Portada (imagen)</label>
                    <input type="file" name="portada" accept="image/*" class="form-control">
                    <?php if (!empty($n['portada'])): ?>
                        <img src="<?= BASE_URL . $n['portada'] ?>" class="rounded mt-2" style="width:200px;height:120px;object-fit:cover">
                    <?php endif; ?>
                </div>


                <div class="col-md-6">
                    <label class="form-label">Medios (múltiples: imágenes, vídeos, audio)</label>
                    <input type="file" name="medios[]" class="form-control" accept="image/*,video/*,audio/*" multiple>
                    <div class="form-text">Se guardan en <code>Assets/media/noticias/</code>.</div>
                </div>


                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="publicar" id="pubChk" <?= ($n['estado'] ?? '') === 'publicado' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="pubChk">Publicar</label>
                    </div>
                </div>


                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary">Guardar</button>
                    <a class="btn btn-outline-light" href="<?= BASE_URL ?>AdminNoticias/index">Cancelar</a>
                </div>
            </form>

        </main>
    </div>
</div>


<?php require BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>