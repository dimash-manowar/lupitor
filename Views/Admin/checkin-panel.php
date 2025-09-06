<?php require BASE_PATH . 'Views/Admin/Templates/headerAdmin.php'; ?>
<?php $t = $t ?? [];
$csrf = $csrf ?? ''; ?>
<!doctype html>
<meta charset="utf-8">
<title>Check-in — <?= htmlspecialchars($t['titulo']) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<div class="container py-3">
    <h1 class="h4">Check-in — <?= htmlspecialchars($t['titulo']) ?></h1>
    <div class="row g-3">
        <div class="col-md-6">
            <div id="reader" style="width:100%;min-height:360px;border:1px dashed #ccc;border-radius:8px;"></div>
            <small class="text-secondary d-block mt-2">Permite la cámara y apunta al QR del jugador.</small>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <div id="res" class="alert alert-secondary">Esperando escaneo…</div>
                    <form class="d-flex gap-2" onsubmit="return false;">
                        <input class="form-control" id="token" placeholder="Pegar token manual">
                        <button class="btn btn-primary" id="btnCheck">Check-in</button>
                    </form>
                </div>
            </div>
            <a href="<?= BASE_URL ?>AdminInscripciones/index?torneo_id=<?= (int)$t['id'] ?>" class="btn btn-outline-light mt-3">Ver listado</a>
        </div>
    </div>
</div>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    const csrf = '<?= htmlspecialchars($csrf) ?>';
    const torneoId = <?= (int)$t['id'] ?>;
    const resBox = document.getElementById('res');

    async function confirmar(token) {
        try {
            const r = await fetch('<?= BASE_URL ?>AdminCheckin/confirmar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    csrf,
                    token,
                    torneo_id: torneoId
                })
            });
            const j = await r.json();
            if (j.ok) {
                resBox.className = 'alert alert-success';
                resBox.textContent = (j.repeat ? '(Ya estaba) ' : '') + (j.nombre ? ('OK: ' + j.nombre) : 'Check-in OK');
            } else {
                resBox.className = 'alert alert-danger';
                resBox.textContent = j.msg || 'Error';
            }
        } catch (e) {
            resBox.className = 'alert alert-danger';
            resBox.textContent = 'Error de red';
        }
    }

    const html5QrCode = new Html5Qrcode("reader");
    Html5Qrcode.getCameras().then(devs => {
        const cam = devs[0]?.id;
        html5QrCode.start(cam, {
                fps: 10,
                qrbox: 250
            },
            (decodedText) => {
                // Permite que el QR contenga la URL completa o solo el token
                try {
                    const u = new URL(decodedText, window.location.origin);
                    confirmar(u.searchParams.get('token') || decodedText);
                } catch {
                    confirmar(decodedText);
                }
            },
            () => {}
        )
    });

    document.getElementById('btnCheck').addEventListener('click', () => {
        const token = (document.getElementById('token').value || '').trim();
        if (token) confirmar(token);
    });
</script>
<?php require BASE_PATH . 'Views/Admin/Templates/footerAdmin.php'; ?>