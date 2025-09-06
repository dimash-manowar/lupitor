<?php
class NotificacionesModel extends Mysql
{
    protected string $tabla = 'notificaciones';

    public function crear(int $usuarioId, string $tipo, string $titulo, ?string $cuerpo = null, ?string $link = null, $meta = null): ?int
    {
        $metaStr = is_array($meta) ? json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $meta;
        $sql = "INSERT INTO {$this->tabla} (usuario_id, tipo, titulo, cuerpo, link_url, meta_json)
                VALUES(?,?,?,?,?,?)";
        return $this->insert($sql, [$usuarioId, $tipo, $titulo, $cuerpo, $link, $metaStr]);
    }

    /** Crea la misma notificación para varios usuarios */
    public function crearParaUsuarios(array $ids, string $tipo, string $titulo, ?string $cuerpo = null, ?string $link = null, $meta = null): int
    {
        $count=0;
        foreach ($ids as $uid) {
            $id = $this->crear((int)$uid, $tipo, $titulo, $cuerpo, $link, $meta);
            if ($id) $count++;
        }
        return $count;
    }

    public function contarNoLeidas(int $usuarioId): int
    {
        $row = $this->select_one("SELECT COUNT(*) AS c FROM {$this->tabla} WHERE usuario_id=? AND leida_en IS NULL", [$usuarioId]);
        return (int)($row['c'] ?? 0);
    }

    public function listarNoLeidas(int $usuarioId, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $sql = "SELECT * FROM {$this->tabla}
                WHERE usuario_id=? AND leida_en IS NULL
                ORDER BY creada_en DESC
                LIMIT {$limit}";
        return $this->select($sql, [$usuarioId]);
    }

    public function listarRecientes(int $usuarioId, int $limit = 20): array
    {
        $limit = max(1, min(50, $limit));
        $sql = "SELECT * FROM {$this->tabla}
                WHERE usuario_id=?
                ORDER BY creada_en DESC
                LIMIT {$limit}";
        return $this->select($sql, [$usuarioId]);
    }

    public function marcarLeida(int $id, int $usuarioId): int
    {
        return $this->update("UPDATE {$this->tabla} SET leida_en = NOW() WHERE id=? AND usuario_id=? AND leida_en IS NULL", [$id, $usuarioId]);
    }

    public function marcarTodasLeidas(int $usuarioId): int
    {
        return $this->update("UPDATE {$this->tabla} SET leida_en = NOW() WHERE usuario_id=? AND leida_en IS NULL", [$usuarioId]);
    }
}
