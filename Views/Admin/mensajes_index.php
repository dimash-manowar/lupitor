<?php require BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<?php
$topUsers = $topUsers ?? [];
$to_id    = (int)($to_id ?? 0);
$hilo     = $hilo ?? [];
?>
<div class="u-card mb-3">
    <div class="row g-2 align-items-center">
        <div class="col-md-4">
            <label class="form-label mb-1">Destinatario</label>
            <select id="destSelect" name="to_id" form="msgForm" class="form-select form-select-sm bg-dark text-light">
                <?php if (empty($topUsers)): ?>
                    <option value="">— Selecciona destinatario —</option>
                <?php else: ?>
                    <optgroup label="Sugerencias">
                        <?php foreach ($topUsers as $u): ?>
                            <option value="<?= (int)$u['id'] ?>" <?= $to_id === (int)$u['id'] ? ' selected' : '' ?>>
                                #<?= (int)$u['id'] ?> — <?= htmlspecialchars($u['nombre'] ?: $u['email']) ?>
                                (<?= htmlspecialchars($u['email']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endif; ?>
                <optgroup label="Resultados de búsqueda" id="destResults"></optgroup>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label mb-1">Buscar por nombre o email</label>
            <input id="destSearch" class="form-control form-control-sm bg-dark text-light" placeholder="Escribe para buscar…">
        </div>
    </div>
</div>

<div class="u-card mb-3">
    <form id="msgForm" class="js-msg-form" method="post" action="<?= BASE_URL ?>UsuarioMensajes/enviarPost" enctype="multipart/form-data">


        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf ?? '') ?>">


        <div class="mb-2">
            <label class="form-label">Mensaje</label>
            <textarea name="body" class="form-control bg-dark text-light js-textarea" rows="3" placeholder="Escribe tu mensaje o pega archivos (Ctrl+V)…"></textarea>
        </div>

        <div class="dropzone js-dropzone">
            <div><i class="bi bi-cloud-arrow-up"></i> Arrastra aquí imágenes / vídeos / audio, o <u>haz clic</u></div>
            <div class="small mt-1 text-secondary">Formatos comunes (JPG/PNG/WebP · MP4/WEBM/MOV · MP3/WAV) · Máx 150 MB/archivo</div>
            <input class="visualmente-oculto js-file" type="file" name="files[]" accept="image/*,video/*,audio/*" multiple>

            <!-- Barra de progreso -->
            <div id="uploadProgressWrap" class="progress mt-2" style="height: 10px;" hidden>
                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                    style="width:0%" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
        </div>

        <div class="uploader-preview js-preview" aria-live="polite"></div>

        <div class="d-flex align-items-center gap-2 mt-2">
            <button id="btnSend" class="btn btn-primary btn-sm" type="button"><i class="bi bi-send"></i> Enviar</button>
            <button class="btn btn-outline-secondary btn-sm js-clear" type="button"><i class="bi bi-x-circle"></i> Vaciar adjuntos</button>
        </div>
    </form>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="table-responsive u-card p-0">
            <table class="table table-dark table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Último</th>
                        <th class="text-center">No leídos</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (($items ?? []) as $c): ?>
                        <?php $unread = ($c['usuario_a_id'] == $_SESSION['user']['id']) ? (int)$c['no_leidos_a'] : (int)$c['no_leidos_b']; ?>
                        <tr>
                            <td class="fw-semibold d-flex align-items-center gap-2">
                                <?php if (!empty($c['otro_foto'])): ?>
                                    <img src="<?= htmlspecialchars($c['otro_foto']) ?>" alt="" width="28" height="28" class="rounded-circle">
                                <?php else: ?>
                                    <span class="badge bg-secondary rounded-circle" style="width:28px;height:28px;display:inline-flex;align-items:center;justify-content:center;">👤</span>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($c['otro_nombre'] ?? ('#' . $c['otro_id'])) ?></span>
                            </td>
                            <td class="text-secondary small">
                                <?= date('d/m/Y H:i', strtotime($c['fecha_ultimo_mensaje'])) ?>
                            </td>
                            <td class="text-center"><?= $unread > 0 ? '<span class="badge bg-info">' . $unread . '</span>' : '<span class="text-secondary">0</span>' ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL . 'UsuarioMensajes/ver/' . (int)$c['otro_id'] ?>">
                                    <i class="bi bi-chat-square-text"></i> Abrir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="4" class="text-secondary p-3">Sin conversaciones.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-lg-7">
        <div id="thread" class="u-card p-3" style="max-height:520px;overflow:auto;">
            <div id="messagesList">
                <?php foreach ($hilo as $fila):
                    $mensaje  = $fila;
                    $adjuntos = $fila['adjuntos'] ?? [];

                    $tpl = rtrim(BASE_PATH, '/\\') . DIRECTORY_SEPARATOR
                        . 'Views' . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR . 'mensaje_item.php';
                    if (!is_file($tpl)) {
                        echo '<div class="alert alert-danger">Falta plantilla: ' . htmlspecialchars($tpl) . '</div>';
                    } else {
                        include $tpl;
                    }
                endforeach; ?>
            </div>
        </div>
    </div>

    <?php require BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>