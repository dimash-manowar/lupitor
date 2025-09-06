<?php require BASE_PATH.'Views/Usuario/Templates/headerUsuario.php'; ?>
<?php
// Variables esperadas: $items, $page, $total_pages, $per, $estado, $csrf
$items = $items ?? []; $page = $page ?? 1; $total_pages = $total_pages ?? 1;
$per = $per ?? 15; $estado = $estado ?? 'todas';

function trunc_str(string $s, int $len=120): string {
  $s = trim($s);
  return (mb_strlen($s,'UTF-8') > $len) ? (mb_substr($s,0,$len,'UTF-8').'…') : $s;
}
?>
<div class="container my-3">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 u-card">
    <div class="d-flex align-items-center gap-2">
      <a class="btn btn-sm <?= $estado==='todas'?'btn-light':'btn-outline-light' ?>"
         href="<?= BASE_URL.'UsuarioNotificaciones/index?estado=todas&per='.$per ?>">
        Todas
      </a>
      <a class="btn btn-sm <?= $estado==='no-leidas'?'btn-light':'btn-outline-light' ?>"
         href="<?= BASE_URL.'UsuarioNotificaciones/index?estado=no-leidas&per='.$per ?>">
        No leídas
      </a>
    </div>

    <div class="d-flex align-items-center gap-2">
      <form method="get" action="<?= BASE_URL ?>UsuarioNotificaciones/index" class="d-flex align-items-center gap-2">
        <input type="hidden" name="estado" value="<?= htmlspecialchars($estado) ?>">
        <label class="text-secondary small">Por página</label>
        <select class="form-select form-select-sm bg-dark text-light border-secondary" name="per" onchange="this.form.submit()">
          <?php foreach ([10,15,20,30,50] as $opt): ?>
            <option value="<?= $opt ?>" <?= $per===$opt?'selected':'' ?>><?= $opt ?></option>
          <?php endforeach; ?>
        </select>
      </form>
      <button id="btnMarkAll" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-check2-all"></i> Marcar todas como leídas
      </button>
    </div>
  </div>

  <?php if (empty($items)): ?>
    <div class="u-card text-secondary">No hay notificaciones.</div>
  <?php else: ?>
    <div class="table-responsive u-card p-0">
      <table class="table table-dark table-striped align-middle mb-0">
        <thead>
          <tr>
            <th style="width:40px;"></th>
            <th>Título</th>
            <th>Mensaje</th>
            <th class="text-nowrap">Fecha</th>
            <th class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($items as $n): ?>
          <?php $isRead = (int)($n['is_read'] ?? 0) === 1; ?>
          <tr data-id="<?= (int)$n['id'] ?>">
            <td class="text-center">
              <?php if ($isRead): ?>
                <i class="bi bi-dot text-secondary" title="Leída"></i>
              <?php else: ?>
                <i class="bi bi-circle-fill text-info" title="No leída" style="font-size:8px;"></i>
              <?php endif; ?>
            </td>
            <td class="fw-semibold"><?= htmlspecialchars($n['titulo'] ?? '') ?></td>
            <td class="text-secondary"><?= htmlspecialchars(trunc_str((string)($n['mensaje'] ?? ''), 140)) ?></td>
            <td class="text-secondary text-nowrap"><?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></td>
            <td class="text-end">
              <?php if (!empty($n['link'])): ?>
                <a class="btn btn-sm btn-outline-light" href="<?= htmlspecialchars($n['link']) ?>" target="_blank" rel="noopener">
                  <i class="bi bi-box-arrow-up-right"></i>
                </a>
              <?php endif; ?>
              <?php if (!$isRead): ?>
                <button class="btn btn-sm btn-outline-secondary js-mark" type="button">
                  <i class="bi bi-check2"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pages > 1): ?>
      <nav class="mt-3">
        <ul class="pagination pagination-sm">
          <?php
            $base = BASE_URL.'UsuarioNotificaciones/index';
            $qsBase = 'estado='.$estado.'&per='.$per;
          ?>
          <?php for ($p=1; $p <= $total_pages; $p++): ?>
            <li class="page-item <?= $p===$page?'active':'' ?>">
              <a class="page-link" href="<?= $base.'/'.$p.'?'.$qsBase ?>"><?= $p ?></a>
            </li>
          <?php endfor; ?>
        </ul>
      </nav>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script>
(function(){
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || window.CSRF_TOKEN || '';

  // Marcar una notificación
  document.querySelectorAll('.js-mark').forEach(btn => {
    btn.addEventListener('click', async () => {
      const tr = btn.closest('tr'); const id = tr?.dataset.id;
      if (!id) return;
      btn.disabled = true;
      try{
        const res = await fetch('<?= BASE_URL ?>UsuarioNotificaciones/marcarLeida', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({ id, csrf })
        });
        const j = await res.json();
        if (j.ok) {
          // Refresco ligero: cambiamos punto azul por gris y ocultamos botón
          const dot = tr.querySelector('.bi-circle-fill');
          if (dot) { dot.classList.remove('text-info'); dot.classList.add('text-secondary'); dot.classList.replace('bi-circle-fill','bi-dot'); }
          btn.remove();
        } else { btn.disabled = false; }
      } catch(e){ btn.disabled = false; }
    });
  });

  // Marcar todas
  const btnAll = document.getElementById('btnMarkAll');
  if (btnAll) {
    btnAll.addEventListener('click', async () => {
      btnAll.disabled = true;
      try{
        const res = await fetch('<?= BASE_URL ?>UsuarioNotificaciones/marcarLeidas', {
          method:'POST',
          headers:{'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({ csrf })
        });
        const j = await res.json();
        if (j.ok) location.reload();
        else btnAll.disabled = false;
      } catch(e){ btnAll.disabled = false; }
    });
  }
})();
</script>

<?php require BASE_PATH.'Views/Usuario/Templates/footerUsuario.php'; ?>
