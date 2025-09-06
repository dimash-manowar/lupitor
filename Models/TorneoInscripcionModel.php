<?php
class TorneoInscripcionModel extends Mysql
{
    protected string $table = 'torneo_inscripciones';

    public function crear(array $d): ?int
    {
        // estado por defecto: 'confirmada' si pago_ok=1, si no 'pendiente'
        $estado = $d['estado'] ?? ((int)($d['pago_ok'] ?? 0) === 1 ? 'confirmada' : 'pendiente');

        $sql = "INSERT INTO {$this->table}
            (torneo_id, nombre, apellidos, direccion, fecha_nac, elo, federado, email, telefono,
             pago_modo, pago_ref, checkin_token, checkin_at, pago_ok, estado, created_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())";

        return $this->insert($sql, [
            (int)$d['torneo_id'],
            $d['nombre'],
            $d['apellidos']   ?? null,
            $d['direccion']   ?? null,
            $d['fecha_nac']   ?? null,
            $d['elo']         ?? null,
            (int)($d['federado'] ?? 0),
            $d['email'],
            $d['telefono']    ?? null,
            $d['pago_modo']   ?? 'ninguno',
            $d['pago_ref']    ?? null,
            $d['checkin_token'] ?? null, // string hex
            null,                         // checkin_at
            (int)($d['pago_ok'] ?? 0),
            $estado
        ]);
    }



    public function listarPorTorneo(int $torneoId, int $page = 1, int $per = 50, ?string $q = null): array
    {
        $page = max(1, $page);
        $per = max(1, $per);
        $off = ($page - 1) * $per;
        $where = "FROM {$this->table} WHERE torneo_id=?";
        $vals = [(int)$torneoId];
        if ($q) {
            $where .= " AND (nombre LIKE ? OR apellidos LIKE ? OR email LIKE ?)";
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
        }
        $items = $this->select("SELECT * {$where} ORDER BY created_at DESC LIMIT {$per} OFFSET {$off}", $vals);
        $row = $this->select_one("SELECT COUNT(*) c {$where}", $vals);
        $total = (int)($row['c'] ?? 0);
        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total, 'total_pages' => (int)ceil($total / $per)];
    }

    public function eliminar(int $id): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }
    public function contarPorTorneo(int $torneoId): int
    {
        $r = $this->select_one("SELECT COUNT(*) c FROM {$this->table} WHERE torneo_id=?", [$torneoId]);
        return (int)($r['c'] ?? 0);
    }
    public function listarPorUsuario(int $userId, int $page = 1, int $per = 15): array
    {
        $page = max(1, $page);
        $per = max(1, $per);
        $off = ($page - 1) * $per;
        $items = $this->select(
            "SELECT i.id, i.torneo_id, i.created_at, i.estado, i.pago_ok, i.pago_modo, i.pago_ref, i.checkin_token,
                t.titulo, t.inicio, t.lugar, t.precio
           FROM {$this->table} i
           JOIN torneos t ON t.id = i.torneo_id
          WHERE i.user_id = ?
          ORDER BY i.created_at DESC
          LIMIT {$per} OFFSET {$off}",
            [$userId]
        );
        $row = $this->select_one(
            "SELECT COUNT(*) c
           FROM {$this->table} i
          WHERE i.user_id = ?",
            [$userId]
        );
        $total = (int)($row['c'] ?? 0);
        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total, 'total_pages' => (int)ceil($total / $per)];
    }

    public function findByIdAndUser(int $id, int $userId): ?array
    {
        return $this->select_one(
            "SELECT i.*, t.titulo AS torneo, t.inicio, t.lugar, t.precio
           FROM {$this->table} i
           JOIN torneos t ON t.id = i.torneo_id
          WHERE i.id = ? AND i.user_id = ?
          LIMIT 1",
            [$id, $userId]
        );
    }
    public function proximoPorUsuario(int $userId): ?array
    {
        return $this->select_one(
            "SELECT i.id, i.checkin_token, i.created_at,
            t.id AS torneo_id, t.slug, t.titulo, t.inicio, t.lugar, t.precio
       FROM {$this->table} i
       JOIN torneos t ON t.id = i.torneo_id
      WHERE i.user_id=? AND t.inicio >= NOW()
      ORDER BY t.inicio ASC
      LIMIT 1",
            [$userId]
        );
    }

    public function ultimaPorUsuario(int $userId): ?array
    {
        return $this->select_one(
            "SELECT i.id, i.torneo_id, i.created_at,
            t.slug, t.titulo, t.inicio
       FROM {$this->table} i
       JOIN torneos t ON t.id = i.torneo_id
      WHERE i.user_id=?
      ORDER BY i.created_at DESC
      LIMIT 1",
            [$userId]
        );
    }
}
