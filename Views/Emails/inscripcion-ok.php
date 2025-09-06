<?php require_once BASE_PATH . "Views/Templates/header.php"; ?>
<?php /* $nombre,$torneo,$fecha,$lugar,$precio,$pago_modo,$pago_ref,$qr_url,$gestion_url */ ?>
<!doctype html>
<meta charset="utf-8">

<body style="font-family:system-ui; background:#f6f7f9; padding:24px;">
    <table width="100%" style="max-width:600px;margin:auto;background:#fff;border-radius:12px;overflow:hidden;border:1px solid #e5e7eb">
        <tr>
            <td style="padding:20px 24px;background:#0d6efd;color:#fff;">
                <h1 style="margin:0;font-size:20px;">Inscripción confirmada</h1>
                <div><?= htmlspecialchars($torneo) ?></div>
            </td>
        </tr>
        <tr>
            <td style="padding:20px 24px;color:#111;">
                <p>Hola <strong><?= htmlspecialchars($nombre) ?></strong>,</p>
                <p>Hemos recibido tu inscripción.</p>
                <ul style="padding-left:18px;line-height:1.6">
                    <li><strong>Fecha:</strong> <?= htmlspecialchars($fecha) ?></li>
                    <?php if (!empty($lugar)): ?><li><strong>Lugar:</strong> <?= htmlspecialchars($lugar) ?></li><?php endif; ?>
                    <li><strong>Cuota:</strong> <?= $precio ?></li>
                    <li><strong>Pago:</strong> <?= htmlspecialchars($pago_modo) ?><?php if ($pago_ref) echo ' · Ref: ' . htmlspecialchars($pago_ref); ?></li>
                </ul>
                <p>Tu <strong>QR de check-in</strong>:</p>
                <?php
                // si se pasó $qr_cid usaremos CID, si no, la URL
                if (!empty($qr_cid)) {
                    echo '<p><img src="cid:' . htmlspecialchars($qr_cid) . '" width="160" height="160" style="border:1px solid #eee;border-radius:8px"></p>';
                } else {
                    // fallback (puede no verse en clientes que bloquean imágenes remotas/localhost)
                    echo '<p><img src="' . htmlspecialchars($qr_url) . '" width="160" height="160" style="border:1px solid #eee;border-radius:8px"></p>';
                }
                ?>
                <p><a href="<?= htmlspecialchars($gestion_url) ?>" style="color:#0d6efd">Gestionar inscripción</a></p>
                <hr style="border:none;border-top:1px solid #eee;margin:16px 0">
                <p style="color:#555;font-size:12px">Club de Ajedrez de Berriozar</p>
            </td>
        </tr>
    </table>
</body>
<?php require_once BASE_PATH . "Views/Templates/footer.php"; ?>