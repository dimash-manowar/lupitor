<?php
class AdminInscripcionesModel extends Mysql
{
    protected string $table = 'torneo_inscripciones';
    public function getTable(): string
    {
        return $this->table;
    }

    public function listarAdmin(
        ?int $torneoId,
        ?string $estado,
        ?string $pago,
        int $page,
        int $per,
        ?string $q,
        ?string $desde,
        ?string $hasta,
        string $orden = 'recientes'
    ): array {
        $page = max(1, $page);
        $per = max(1, $per);
        $off = ($page - 1) * $per;

        $orderMap = [
            'recientes'   => 'i.created_at DESC',
            'antiguos'    => 'i.created_at ASC',
            'nombre_asc'  => 'i.nombre ASC, i.apellidos ASC',
            'nombre_desc' => 'i.nombre DESC, i.apellidos DESC',
        ];
        $orderBy = $orderMap[$orden] ?? $orderMap['recientes'];

        $where = "FROM {$this->table} i
              JOIN torneos t ON t.id=i.torneo_id
              WHERE 1";
        $vals = [];

        if ($torneoId) {
            $where .= " AND i.torneo_id=?";
            $vals[] = $torneoId;
        }
        if ($estado) {
            $where .= " AND i.estado=?";
            $vals[] = $estado;
        }
        if ($pago === 'ok') {
            $where .= " AND i.pago_ok=1";
        }
        if ($pago === 'pend') {
            $where .= " AND i.pago_ok=0";
        }
        if ($q) {
            $where .= " AND (i.nombre LIKE ? OR i.apellidos LIKE ? OR i.email LIKE ? OR i.pago_ref LIKE ? OR t.titulo LIKE ?)";
            $vals[] = "%$q%";
            $vals[] = "%$q%";
            $vals[] = "%$q%";
            $vals[] = "%$q%";
            $vals[] = "%$q%";
        }
        if ($desde) {
            $where .= " AND i.created_at >= ?";
            $vals[] = "$desde 00:00:00";
        }
        if ($hasta) {
            $where .= " AND i.created_at <= ?";
            $vals[] = "$hasta 23:59:59";
        }

        // 👇 Campos que el PDF necesita SÍ o SÍ
        $sqlItems = "SELECT
         i.id, i.torneo_id,
         t.titulo AS torneo,
         i.nombre, i.apellidos, i.email, i.telefono,
         i.elo, i.federado,
         i.pago_ok, i.pago_modo, i.pago_ref,
         i.estado, i.created_at
       {$where}
       ORDER BY {$orderBy}
       LIMIT {$per} OFFSET {$off}";

        $items = $this->select($sqlItems, $vals);
        $row   = $this->select_one("SELECT COUNT(*) c {$where}", $vals);
        $total = (int)($row['c'] ?? 0);

        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total, 'total_pages' => (int)ceil($total / $per)];
    }

    public function listarParaPdf(
        ?int $torneoId,
        ?string $estado,
        ?string $pago,
        ?string $q,
        ?string $desde,
        ?string $hasta,
        string $orden = 'recientes'
    ): array {
        $r = $this->listarAdmin($torneoId, $estado, $pago, 1, 5000, $q, $desde, $hasta, $orden);
        return $r['items'] ?? [];
    }

    public function cambiarEstado(int $id, string $estado): int
    {
        if (!in_array($estado, ['pendiente', 'confirmada', 'anulada'], true)) return 0;
        return $this->update("UPDATE {$this->table} SET estado=?, updated_at=NOW() WHERE id=?", [$estado, $id]);
    }

    public function eliminar(int $id): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }

    public function torneosOptions(): array
    {
        return $this->select("SELECT id, titulo FROM torneos ORDER BY inicio DESC");
    }
    public function setPagoOk(int $id, int $ok): int
    {
        return $this->update("UPDATE {$this->table} SET pago_ok=?, updated_at=NOW() WHERE id=?", [$ok ? 1 : 0, $id]);
    }

    public function contarPorEstado(
        ?int $torneoId,
        ?string $pago,           // 'ok' | 'pend' | null
        ?string $q,
        ?string $desde,
        ?string $hasta
    ): array {
        $stats = ['pendiente' => 0, 'confirmada' => 0, 'anulada' => 0];

        $where = "FROM {$this->table} i JOIN torneos t ON t.id=i.torneo_id WHERE 1";
        $vals = [];
        if ($torneoId) {
            $where .= " AND i.torneo_id=?";
            $vals[] = $torneoId;
        }
        if ($pago === 'ok')   $where .= " AND i.pago_ok=1";
        if ($pago === 'pend') $where .= " AND i.pago_ok=0";
        if ($q) {
            $where .= " AND (i.nombre LIKE ? OR i.apellidos LIKE ? OR i.email LIKE ? OR i.pago_ref LIKE ?)";
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
        }
        if ($desde) {
            $where .= " AND i.created_at >= ?";
            $vals[] = $desde . ' 00:00:00';
        }
        if ($hasta) {
            $where .= " AND i.created_at <= ?";
            $vals[] = $hasta . ' 23:59:59';
        }

        $rows = $this->select("SELECT i.estado, COUNT(*) c {$where} GROUP BY i.estado", $vals);
        foreach ($rows as $r) {
            $k = $r['estado'];
            if (isset($stats[$k])) $stats[$k] = (int)$r['c'];
        }
        return $stats;
    }
    /** Torneos que empiezan ese día (YYYY-mm-dd) */
    public function torneosHoy(string $fechaYmd): array
    {
        return $this->select(
            "SELECT id, titulo, inicio, lugar, precio
               FROM torneos
              WHERE DATE(inicio)=? AND estado='publicado'",
            [$fechaYmd]
        );
    }

    /** Inscripciones pendientes de recordatorio para un torneo */
    public function inscripcionesParaRecordatorio(int $torneoId): array
    {
        return $this->select(
            "SELECT id, nombre, apellidos, email, telefono, checkin_token
               FROM {$this->table}
              WHERE torneo_id=? AND estado<>'anulada' AND reminder_sent=0",
            [$torneoId]
        );
    }

    /** Marca como enviados los recordatorios de un torneo */
    public function marcarRecordatoriosEnviados(int $torneoId): int
    {
        return $this->update(
            "UPDATE {$this->table}
                SET reminder_sent=1, updated_at=NOW()
              WHERE torneo_id=? AND reminder_sent=0",
            [$torneoId]
        );
    }

    /** Datos completos para el recibo PDF de una inscripción */
    public function getReciboData(int $id): ?array
    {
        return $this->select_one(
            "SELECT i.*, t.titulo AS torneo, t.inicio, t.lugar, t.precio
               FROM {$this->table} i
               JOIN torneos t ON t.id=i.torneo_id
              WHERE i.id=?",
            [$id]
        );
    }
}
