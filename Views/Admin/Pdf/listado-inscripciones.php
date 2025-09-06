<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 18mm 14mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #fff !important;
            color: #111 !important;
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        /* Fuerza negro para evitar herencias oscuras del sitio */
        * {
            color: #111 !important;
        }

        h2 {
            margin: 0 0 10px;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #f2f2f2;
            font-weight: 700;
        }

        tr {
            page-break-inside: avoid;
        }

        .small {
            font-size: 11px;
            color: #555 !important;
        }

        .footer {
            margin-top: 10px;
            border-top: 1px solid #ccc;
            padding-top: 6px;
            font-size: 11px;
            color: #555 !important;
        }
    </style>
</head>

<body>
    <h2>Listado de inscripciones</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Torneo</th>
                <th>Jugador</th>
                <th>Email</th>
                <th>ELO</th>
                <th>Federado</th>
                <th>Pago</th>
                <th>Estado</th>
                <th>Alta</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($items ?? []) as $r): ?>
                <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= htmlspecialchars($r['torneo'] ?? '') ?></td>
                    <td><?= htmlspecialchars(trim(($r['nombre'] ?? '') . ' ' . ($r['apellidos'] ?? ''))) ?></td>
                    <td><?= htmlspecialchars($r['email'] ?? '') ?></td>
                    <td><?= $r['elo'] ?: '-' ?></td>
                    <td><?= ((int)($r['federado'] ?? 0) === 1 ? 'Sí' : 'No') ?></td>
                    <td><?= ((int)($r['pago_ok'] ?? 0) === 1 ? 'OK' : 'Pend.') . ' (' . htmlspecialchars($r['pago_modo'] ?? '') . ')' ?></td>
                    <td><?= htmlspecialchars($r['estado'] ?? '') ?></td>
                    <td><?= isset($r['created_at']) ? date('d/m/Y H:i', strtotime($r['created_at'])) : '' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="footer">© <?= date('Y') ?> Club de Ajedrez de Berriozar</div>
</body>

</html>