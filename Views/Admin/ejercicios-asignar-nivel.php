<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<h2 class="mb-3">Asignar por nivel</h2>
<div class="row g-3">
  <?php foreach ($niveles as $niv): ?>
    <div class="col-lg-4">
      <div class="card bg-dark border-secondary h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
          <strong><?= htmlspecialchars($niv) ?></strong>
          <span class="badge bg-info"><?= (int)($counts[$niv] ?? 0) ?> alumnos</span>
        </div>
        <div class="card-body">
          <label class="form-label">Ejercicios (<?= htmlspecialchars($niv) ?>)</label>
          <select class="form-select js-ej" multiple size="8" data-nivel="<?= htmlspecialchars($niv) ?>">
            <?php foreach (($ej_por_nivel[$niv] ?? []) as $e): ?>
              <option value="<?= (int)$e['id'] ?>"><?= htmlspecialchars($e['titulo']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="mt-2">
            <label class="form-label">Fecha límite (opcional)</label>
            <input type="date" class="form-control js-fecha" data-nivel="<?= htmlspecialchars($niv) ?>">
          </div>
          <div class="mt-2">
            <label class="form-label">Puntos (opcional)</label>
            <input type="number" class="form-control js-puntos" data-nivel="<?= htmlspecialchars($niv) ?>" min="0" value="0">
          </div>
        </div>
        <div class="card-footer text-end">
          <button class="btn btn-primary js-asignar" data-nivel="<?= htmlspecialchars($niv) ?>">Asignar a este nivel</button>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<script>
(() => {
  const csrf = <?= json_encode($csrf) ?>;
  document.querySelectorAll('.js-asignar').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      const nivel = btn.dataset.nivel;
      const sel   = document.querySelector(`.js-ej[data-nivel="${nivel}"]`);
      const fecha = document.querySelector(`.js-fecha[data-nivel="${nivel}"]`)?.value || '';
      const puntos= document.querySelector(`.js-puntos[data-nivel="${nivel}"]`)?.value || '0';
      const ids = Array.from(sel?.selectedOptions || []).map(o=>o.value);
      if (!ids.length) { return Swal.fire({icon:'info',title:'Elige ejercicios'}); }
      btn.disabled = true;
      try{
        const res = await fetch('<?= BASE_URL ?>admin/ejerciciosAsignarNivelPost', {
          method: 'POST',
          headers: {'Content-Type':'application/x-www-form-urlencoded'},
          body: new URLSearchParams({ csrf, nivel, fecha_limite:fecha, puntos, 'ejercicio_ids[]': ids })
        });
        const j = await res.json();
        if (!j.ok) throw new Error(j.error||'Error');
        Swal.fire({icon:'success',title:'Asignado',text:`Filas afectadas: ${j.asignados}`});
      }catch(e){
        Swal.fire({icon:'error',title:'No se pudo asignar',text:String(e.message||e)});
      }finally{ btn.disabled = false; }
    });
  });
})();
</script>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
