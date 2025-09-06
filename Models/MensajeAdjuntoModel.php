<?php
class MensajeAdjuntoModel extends Mysql
{
    protected string $tabla = 'mensajes_archivos';

    /**
     * INSERT: crea un adjunto y devuelve el ID insertado (o null si falla).
     */
    public function crear(
        int $mensajeId,
        string $tipo,          // 'imagen'|'video'|'audio'|'archivo'
        string $ruta,          // ruta relativa (p.ej. 'Assets/uploads/mensajes/abc.webp')
        string $mime,          // MIME detectado por el controlador
        int $tamanio,          // bytes
        ?int $ancho = null,    // solo imágenes
        ?int $alto = null,     // solo imágenes
        ?float $duracion = null// solo audio/vídeo (segundos)
    ): ?int {
        return $this->insert(
            "INSERT INTO {$this->tabla}
             (mensaje_id, tipo, ruta, mime, tamanio, ancho, alto, duracion)
             VALUES (?,?,?,?,?,?,?,?)",
            [$mensajeId, $tipo, $ruta, $mime, $tamanio, $ancho, $alto, $duracion]
        );
    }

    /**
     * SELECT one: adjunto por ID (o null).
     */
    public function obtenerPorId(int $id): ?array
    {
        return $this->select_one(
            "SELECT * FROM {$this->tabla} WHERE id=? LIMIT 1",
            [$id]
        );
    }

    /**
     * SELECT list: adjuntos de un mensaje (orden por id asc).
     */
    public function obtenerPorMensaje(int $mensajeId, int $limit = 200, int $offset = 0): array
    {
        $limit  = max(1, min(1000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT * FROM {$this->tabla}
                WHERE mensaje_id=?
                ORDER BY id ASC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->select($sql, [$mensajeId]);
    }

    /**
     * SELECT list: adjuntos de varios mensajes.
     * Devuelve array plano con todos los registros.
     */
    public function obtenerPorMensajes(array $mensajeIds): array
    {
        $mensajeIds = array_values(array_unique(array_map('intval', $mensajeIds)));
        if (empty($mensajeIds)) return [];
        [$inSql, $params] = placeholdersIN('mensaje_id', $mensajeIds);
        $sql = "SELECT * FROM {$this->tabla} WHERE {$inSql} ORDER BY mensaje_id ASC, id ASC";
        return $this->select($sql, $params);
    }

    /**
     * SELECT list (JOIN): adjuntos de una conversación completa.
     */
    public function listarPorConversacion(int $conversacionId, int $limit = 1000, int $offset = 0): array
    {
        $limit  = max(1, min(5000, (int)$limit));
        $offset = max(0, (int)$offset);
        $sql = "SELECT a.*
                FROM {$this->tabla} a
                JOIN mensajes m ON m.id = a.mensaje_id
                WHERE m.conversacion_id = ?
                ORDER BY a.id ASC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->select($sql, [$conversacionId]);
    }

    /**
     * UPDATE: campos permitidos = tipo, ruta, mime, tamanio, ancho, alto, duracion.
     * Devuelve filas afectadas.
     */
    public function actualizar(int $id, array $campos): int
    {
        if (empty($campos)) return 0;
        $permitidos = ['tipo','ruta','mime','tamanio','ancho','alto','duracion'];
        $sets = [];
        $vals = [];
        foreach ($campos as $k => $v) {
            if (!in_array($k, $permitidos, true)) continue;
            $sets[] = "{$k} = ?";
            $vals[] = $v;
        }
        if (empty($sets)) return 0;
        $vals[] = $id;

        $sql = "UPDATE {$this->tabla} SET ".implode(', ', $sets)." WHERE id = ?";
        return $this->update($sql, $vals);
    }

    /**
     * DELETE: por ID. Devuelve filas afectadas.
     */
    public function eliminarPorId(int $id): int
    {
        return $this->delete("DELETE FROM {$this->tabla} WHERE id = ?", [$id]);
    }

    /**
     * DELETE: por mensaje. Devuelve filas afectadas.
     */
    public function eliminarPorMensaje(int $mensajeId): int
    {
        return $this->delete("DELETE FROM {$this->tabla} WHERE mensaje_id = ?", [$mensajeId]);
    }

    /**
     * DELETE: por conversación (subselect). Devuelve filas afectadas.
     */
    public function eliminarPorConversacion(int $conversacionId): int
    {
        $sql = "DELETE a FROM {$this->tabla} a
                JOIN mensajes m ON m.id = a.mensaje_id
                WHERE m.conversacion_id = ?";
        return $this->delete($sql, [$conversacionId]);
    }

    /**
     * BÚSQUEDA simple por filtros.
     * $filtros soporta: tipo (string|array), mime_like (string), mensaje_id (int), conversacion_id (int)
     */
    public function buscar(array $filtros = [], int $page = 1, int $per = 20): array
    {
        $page = max(1, (int)$page);
        $per  = max(1, min(100, (int)$per));
        $off  = ($page - 1) * $per;

        $where = [];
        $vals  = [];

        if (!empty($filtros['mensaje_id'])) {
            $where[] = 'a.mensaje_id = ?';
            $vals[]  = (int)$filtros['mensaje_id'];
        }
        if (!empty($filtros['conversacion_id'])) {
            $where[] = 'm.conversacion_id = ?';
            $vals[]  = (int)$filtros['conversacion_id'];
        }
        if (!empty($filtros['tipo'])) {
            $tipos = is_array($filtros['tipo']) ? $filtros['tipo'] : [$filtros['tipo']];
            [$in, $p] = placeholdersIN('a.tipo', $tipos);
            $where[] = $in;
            $vals = array_merge($vals, $p);
        }
        if (!empty($filtros['mime_like'])) {
            $where[] = 'a.mime LIKE ?';
            $vals[]  = '%'.$filtros['mime_like'].'%';
        }

        $whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';
        $sql = "SELECT a.*
                FROM {$this->tabla} a
                LEFT JOIN mensajes m ON m.id = a.mensaje_id
                {$whereSql}
                ORDER BY a.id DESC
                LIMIT {$per} OFFSET {$off}";
        return $this->select($sql, $vals);
    }

    /**
     * Conteo rápido por mensaje.
     */
    public function contarPorMensaje(int $mensajeId): int
    {
        $row = $this->select_one("SELECT COUNT(*) AS n FROM {$this->tabla} WHERE mensaje_id = ?", [$mensajeId]);
        return (int)($row['n'] ?? 0);
    }
}
