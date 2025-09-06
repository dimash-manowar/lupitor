<?php
class UsuarioNotificacionesModel extends Mysql
{
    protected string $table = 'notificaciones';

    public function contarNoLeidasUsuario(int $userId): int
    {
        $row = $this->select_one("SELECT COUNT(*) c FROM {$this->table} WHERE user_id=? AND is_read=0", [$userId]);
        return (int)($row['c'] ?? 0);
    }

    public function listarRecientesUsuario(int $userId, int $limit = 5): array
    {
        $limit = max(1, min(100, $limit));
        return $this->select("SELECT id, titulo, mensaje, link, is_read, created_at
                            FROM {$this->table}
                           WHERE user_id=?
                           ORDER BY created_at DESC
                           LIMIT {$limit}", [$userId]);
    }

    /* === NUEVOS para el listado con paginación === */
    public function listarUsuarioPaginado(int $userId, int $page = 1, int $per = 15, bool $soloNoLeidas = false): array
    {
        $page = max(1, $page);
        $per = max(1, min(100, $per));
        $off = ($page - 1) * $per;
        $where = "WHERE user_id=?";
        $args = [$userId];
        if ($soloNoLeidas) {
            $where .= " AND is_read=0";
        }
        $items = $this->select(
            "SELECT id, titulo, mensaje, link, is_read, created_at
         FROM {$this->table}
        {$where}
        ORDER BY created_at DESC
        LIMIT {$per} OFFSET {$off}",
            $args
        );
        $row = $this->select_one("SELECT COUNT(*) c FROM {$this->table} {$where}", $args);
        $total = (int)($row['c'] ?? 0);
        return ['items' => $items, 'page' => $page, 'per' => $per, 'total' => $total, 'total_pages' => (int)ceil($total / $per)];
    }

    public function marcarLeidaUsuario(int $id, int $userId): int
    {
        return $this->update("UPDATE {$this->table} SET is_read=1 WHERE id=? AND user_id=? AND is_read=0", [$id, $userId]);
    }

    public function marcarTodasLeidasUsuario(int $userId): int
    {
        return $this->update("UPDATE {$this->table} SET is_read=1 WHERE user_id=? AND is_read=0", [$userId]);
    }
}
