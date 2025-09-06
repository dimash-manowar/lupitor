<?php require BASE_PATH.'Views/Admin/Templates/headerAdmin.php'; ?>
<div class="col-md-6">
    <label class="form-label">Icono (Bootstrap Icons)</label>
    <div class="input-group">
        <span class="input-group-text"><i id="icon-preview" class="bi <?= htmlspecialchars($c['icono'] ?? 'bi-info-circle') ?>"></i></span>
        <input name="icono" id="icon-input" class="form-control" list="icons-list"
            placeholder="bi-people, bi-newspaper..." value="<?= htmlspecialchars($c['icono'] ?? '') ?>">
        <div class="col-12">
            <div id="card-preview" class="card border-0 shadow rounded-4 mt-2">
                <div class="card-body text-center">
                    <i class="bi <?= htmlspecialchars($c['icono'] ?? 'bi-info-circle') ?> display-6 mb-2"></i>
                    <div class="fw-semibold"><?= htmlspecialchars($c['titulo'] ?? 'Título') ?></div>
                    <small class="text-muted d-block"><?= htmlspecialchars($c['descripcion'] ?? 'Descripción…') ?></small>
                </div>
            </div>
        </div>
        <datalist id="icons-list">
            <option value="bi-house-door">
            <option value="bi-people">
            <option value="bi-person-video3">
            <option value="bi-mortarboard">
            <option value="bi-newspaper">
            <option value="bi-calendar-event">
            <option value="bi-trophy">
            <option value="bi-collection-play">
            <option value="bi-camera-video">
            <option value="bi-mic">
            <option value="bi-flag">
            <option value="bi-star">
            <option value="bi-info-circle">
            <option value="bi-geo-alt">
            <option value="bi-briefcase">
            <option value="bi-box-arrow-up-right">
        </datalist>
    </div>
    <div class="form-text">
        Catálogo completo: <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener">Bootstrap Icons</a>
    </div>
</div>
<?php require BASE_PATH.'Views/Admin/Templates/footerAdmin.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const bg = document.querySelector('input[name="color_fondo"]');
        const tx = document.querySelector('input[name="color_texto"]');
        const pv = document.getElementById('card-preview');
        const ic = document.getElementById('icon-input');
        const ttl = document.querySelector('input[name="titulo"]');
        const dsc = document.querySelector('textarea[name="descripcion"]');

        const paint = () => {
            pv.style.background = bg.value || '#0d6efd';
            pv.style.color = tx.value || '#ffffff';
            pv.querySelector('i').className = 'bi ' + (ic.value || 'bi-info-circle') + ' display-6 mb-2';
            pv.querySelector('.fw-semibold').textContent = ttl.value || 'Título';
            pv.querySelector('.text-muted').textContent = dsc.value || 'Descripción…';
        };
        [bg, tx, ic, ttl, dsc].forEach(el => el && el.addEventListener('input', paint));
        paint();
        const input = document.getElementById('icon-input');
        const prev = document.getElementById('icon-preview');
        if (!input || !prev) return;
        const update = () => {
            const val = (input.value || '').trim();
            prev.className = 'bi ' + (val || 'bi-info-circle');
        };
        input.addEventListener('input', update);
        update();
    });
</script>