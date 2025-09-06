<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <style>
    @page {
      margin: 16mm 14mm;
    }

    html,
    body {
      margin: 0;
      padding: 0;
      background: #fff !important;
      color: #111 !important;
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 12px;
      line-height: 1.45;
    }

    * {
      color: #111 !important;
    }

    h1 {
      font-size: 16px;
      margin: 0 0 8px;
    }

    .box {
      border: 1px solid #333;
      padding: 10px;
      border-radius: 6px;
    }

    hr {
      border: none;
      border-top: 1px solid #ccc;
      margin: 8px 0;
    }

    small {
      color: #555 !important;
    }
  </style>
</head>

<body>
  <?php $r = $r ?? []; ?>
  <?php
  $r = $r ?? [];
  $estadoRaw = strtolower(trim((string)($r['estado'] ?? '')));
  $estadoMap = [
    'pendiente'  => 'Pendiente',
    'confirmada' => 'Confirmada',
    'confirmado' => 'Confirmada', // por si tienes registros antiguos
    'anulada'    => 'Anulada',
  ];
  $estadoTxt = $estadoMap[$estadoRaw] ?? ($estadoRaw !== '' ? ucfirst($estadoRaw) : '');
  ?>
  <h1>Recibo de inscripción</h1>
  <div class="box">
    <strong>Torneo:</strong> <?= htmlspecialchars($r['torneo'] ?? '') ?><br>
    <strong>Fecha:</strong> <?= isset($r['inicio']) ? date('d/m/Y H:i', strtotime($r['inicio'])) : '' ?><br>
    <?php if (!empty($r['lugar'])): ?><strong>Lugar:</strong> <?= htmlspecialchars($r['lugar']) ?><br><?php endif; ?>
    <hr>
    <strong>Jugador:</strong> <?= htmlspecialchars(trim(($r['nombre'] ?? '') . ' ' . ($r['apellidos'] ?? ''))) ?><br>
    <strong>Email:</strong> <?= htmlspecialchars($r['email'] ?? '') ?><br>
    <strong>ELO:</strong> <?= $r['elo'] ?: '-' ?><br>
    <strong>Federado:</strong> <?= ((int)($r['federado'] ?? 0) === 1 ? 'Sí' : 'No') ?><br>
    <hr>
    <strong>Cuota:</strong> <?= ((float)($r['precio'] ?? 0) > 0) ? '€' . number_format((float)$r['precio'], 2, ',', '.') : 'Gratuito' ?><br>
    <strong>Pago:</strong> <?= htmlspecialchars($r['pago_modo'] ?? '') ?><?php if (!empty($r['pago_ref'])) echo ' · Ref: ' . htmlspecialchars($r['pago_ref']); ?><br>
    <strong>Estado:</strong> <?= htmlspecialchars($estadoTxt) ?><br>
    <strong>Fecha inscripción:</strong> <?= isset($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '' ?><br>
    <?php if (!empty($r['checkin_at'])): ?><strong>Check-in:</strong> <?= date('d/m/Y H:i', strtotime($r['checkin_at'])) ?><br><?php endif; ?>
  </div>
  <small>Nº <?= (int)($r['id'] ?? 0) ?> — Club de Ajedrez de Berriozar</small>
</body>

</html>