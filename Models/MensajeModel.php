<?php
class MensajeModel extends Mysql
{
    protected string $tabla = 'mensajes';




    public function eliminarPorId(int $id): int
    {
        return $this->delete("DELETE FROM {$this->tabla} WHERE id = ?", [$id]);
    }

    public function crear(int $conversacionId, int $remitenteId, ?string $cuerpo): ?int
    {
        return $this->insert(
            "INSERT INTO {$this->tabla}(conversacion_id, remitente_id, cuerpo) VALUES (?,?,?)",
            [$conversacionId, $remitenteId, $cuerpo]
        );
    }

    public function obtenerPorId(int $id): ?array
    {
        $sql = "SELECT m.*,
                       m.creado_en AS created_at
                FROM {$this->tabla} m
                WHERE m.id=? LIMIT 1";
        return $this->select_one($sql, [$id]);
    }

    public function listarPorConversacion(int $conversacionId, int $limit = 200, int $offset = 0): array
    {
        $limit  = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);

        $sql = "SELECT m.*,
                       m.creado_en AS created_at,
                       u.nombre, u.email, u.foto_url
                FROM {$this->tabla} m
                JOIN usuarios u ON u.id = m.remitente_id
                WHERE m.conversacion_id = ?
                ORDER BY created_at ASC, m.id ASC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->select($sql, [$conversacionId]);
    }

    
}
