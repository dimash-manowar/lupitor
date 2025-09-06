<?php
// Compatibilidad básica
if (!isset($mensaje) || !is_array($mensaje)) $mensaje = [];
if (!isset($adjuntos) || !is_array($adjuntos)) $adjuntos = [];

$esMio   = ((int)($mensaje['remitente_id'] ?? 0) === (int)($_SESSION['user']['id'] ?? 0));
$esAdmin = (strtolower((string)($_SESSION['user']['rol'] ?? '')) === 'admin');
$puede   = $esMio || $esAdmin;
?>
<div id="msg-<?= (int)($mensaje['id'] ?? 0) ?>" data-msg-id="<?= (int)($mensaje['id'] ?? 0) ?>"
     class="mb-3 d-flex <?= $esMio ? 'justify-content-end' : 'justify-content-start' ?>">
  <div class="p-2 rounded-4 <?= $esMio ? 'bg-primary text-white' : 'bg-dark text-light' ?>" style="max-width:75%">
    <div class="d-flex justify-content-between align-items-center mb-1">
      <div class="small opacity-75">
        <?= htmlspecialchars($mensaje['nombre'] ?? ($esMio ? 'Tú' : '')) ?>
        · <?= !empty($mensaje['created_at']) ? date('d/m/Y H:i', strtotime($mensaje['created_at'])) : '' ?>
      </div>
      <?php if ($puede && !empty($mensaje['id'])): ?>
        <button class="btn btn-sm btn-outline-light"
                data-action="del-msg" data-id="<?= (int)$mensaje['id'] ?>">
          <i class="bi bi-trash"></i>
        </button>
      <?php endif; ?>
    </div>

    <?php if (!empty($mensaje['cuerpo'])): ?>
      <div class="mb-2" style="white-space:pre-wrap;word-wrap:break-word;">
        <?= nl2br(htmlspecialchars($mensaje['cuerpo'])) ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($adjuntos)): ?>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($adjuntos as $a): ?>
          <div id="adj-<?= (int)($a['id'] ?? 0) ?>" data-adj-id="<?= (int)($a['id'] ?? 0) ?>" class="position-relative">
            <?php if (($a['tipo'] ?? '') === 'imagen'): ?>
              <a href="<?= BASE_URL . ($a['ruta'] ?? '') ?>" target="_blank" rel="noopener">
                <img src="<?= BASE_URL . ($a['ruta'] ?? '') ?>" alt="" class="rounded border" style="max-width:180px;height:auto;">
              </a>
            <?php elseif (($a['tipo'] ?? '') === 'video'): ?>
              <video src="<?= BASE_URL . ($a['ruta'] ?? '') ?>" controls style="max-width:260px"></video>
            <?php elseif (($a['tipo'] ?? '') === 'audio'): ?>
              <audio src="<?= BASE_URL . ($a['ruta'] ?? '') ?>" controls></audio>
            <?php else: ?>
              <a class="btn btn-sm btn-outline-light" href="<?= BASE_URL . ($a['ruta'] ?? '') ?>" target="_blank" rel="noopener">Descargar</a>
            <?php endif; ?>
            <?php if ($puede && !empty($a['id'])): ?>
              <button class="btn btn-sm btn-outline-light position-absolute top-0 end-0"
                      data-action="del-adj" data-id="<?= (int)$a['id'] ?>">
                <i class="bi bi-x-lg"></i>
              </button>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
