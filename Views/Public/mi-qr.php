<?php require BASE_PATH . 'Views/Templates/header.php'; ?>
<?php $r=$r??[]; $token=$token??''; ?>
<!doctype html><meta charset="utf-8">
<title>QR — <?= htmlspecialchars($r['torneo']) ?></title>
<div style="max-width:480px;margin:24px auto;text-align:center;font-family:system-ui;">
  <h1 style="font-size:20px;margin-bottom:8px;"><?= htmlspecialchars($r['torneo']) ?></h1>
  <div style="color:#555;margin-bottom:12px;"><?= date('d/m/Y H:i', strtotime($r['inicio'])) ?><?php if($r['lugar']) echo ' · '.htmlspecialchars($r['lugar']); ?></div>
  <img src="<?= BASE_URL ?>Inscripcion/qr?token=<?= urlencode($token) ?>" width="240" height="240" style="border:1px solid #eee;border-radius:8px">
  <p style="margin-top:10px">Muestra este código en el acceso para hacer <strong>check-in</strong>.</p>
</div>
<?php require BASE_PATH . 'Views/Templates/footer.php'; ?>
