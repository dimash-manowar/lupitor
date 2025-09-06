<?php
class EjerciciosModel extends Mysql
{
    protected string $table = 'ejercicios';

    public function crear(array $d): ?int
    {
        $esPublico = isset($d['es_publico']) ? (int)!!$d['es_publico'] : (
            (isset($d['visibilidad']) && $d['visibilidad'] === 'publico') ? 1 : 0
        );
        $vis = $esPublico ? 'publico' : ($d['visibilidad'] ?? 'privado');

        $sql = "INSERT INTO {$this->table}
            (titulo,slug,descripcion,tipo,fen,pgn,youtube_id,video_url,
             dificultad,es_publico,visibilidad,publicado,creado_por)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['titulo'],
            $d['slug'],
            $d['descripcion'] ?? null,
            $d['tipo'] ?? 'tactica',
            $d['fen'] ?? null,
            $d['pgn'] ?? null,
            $d['youtube_id'] ?? null,
            $d['video_url'] ?? null,
            $d['dificultad'] ?? 'media',
            $esPublico,
            $vis,
            (int)($d['publicado'] ?? 1),
            $d['creado_por'] ?? null
        ]);
    }

    public function actualizar(int $id, array $d): int
    {
        $esPublico = isset($d['es_publico']) ? (int)!!$d['es_publico'] : null;
        // Si no viene 'es_publico', intenta derivarlo de 'visibilidad'
        if ($esPublico === null && isset($d['visibilidad'])) {
            $esPublico = ($d['visibilidad'] === 'publico') ? 1 : 0;
        }
        // Determina visibilidad coherente
        $vis = isset($d['visibilidad'])
            ? $d['visibilidad']
            : (($esPublico === 1) ? 'publico' : 'privado');

        $sql = "UPDATE {$this->table} SET
              titulo=?, slug=?, descripcion=?, tipo=?, fen=?, pgn=?, youtube_id=?, video_url=?,
              dificultad=?, es_publico=?, visibilidad=?, publicado=?, actualizado_por=?, updated_at=NOW()
            WHERE id=?";
        return $this->update($sql, [
            $d['titulo'],
            $d['slug'],
            $d['descripcion'] ?? null,
            $d['tipo'] ?? 'tactica',
            $d['fen'] ?? null,
            $d['pgn'] ?? null,
            $d['youtube_id'] ?? null,
            $d['video_url'] ?? null,
            $d['dificultad'] ?? 'media',
            (int)($esPublico ?? 0),
            $vis,
            (int)($d['publicado'] ?? 1),
            $d['actualizado_por'] ?? null,
            $id
        ]);
    }


    public function porId(int $id): ?array
    {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?", [$id]);
    }

    public function listarPublicos(int $limit = 20): array
    {
        return $this->select(
            "SELECT * FROM {$this->table}
          WHERE publicado=1
            AND (es_publico=1 OR visibilidad='publico')
        ORDER BY created_at DESC
        LIMIT {$limit}"
        );
    }

    public function listarAsignadosUsuario(int $userId): array
    {
        // Privados asignados + (opcional) públicos
        return $this->select(
            "SELECT e.*, a.disponible_desde, a.disponible_hasta
               FROM ejercicios e
               JOIN ejercicios_asignaciones a ON a.ejercicio_id=e.id
              WHERE a.user_id=? AND e.publicado=1
                AND (a.disponible_desde IS NULL OR a.disponible_desde<=NOW())
                AND (a.disponible_hasta IS NULL OR a.disponible_hasta>=NOW())
           ORDER BY COALESCE(a.disponible_desde,e.created_at) DESC",
            [$userId]
        );
    }

    /* Un ejercicio concreto, solo si está asignado a ese usuario y disponible */
    public function getAsignadoParaUsuario(int $ejercicioId, int $userId): ?array
    {
        return $this->select_one(
            "SELECT e.*, a.user_id, a.disponible_desde, a.disponible_hasta
               FROM ejercicios e
               JOIN ejercicios_asignaciones a ON a.ejercicio_id = e.id
              WHERE e.id = ?
                AND a.user_id = ?
                AND e.publicado = 1
                AND (a.disponible_desde IS NULL OR a.disponible_desde <= NOW())
                AND (a.disponible_hasta IS NULL OR a.disponible_hasta >= NOW())
              LIMIT 1",
            [$ejercicioId, $userId]
        );
    }
    public function listarIdsPorNivel(string $nivel): array
    {
        $sql = "SELECT id FROM ejercicios_publicos WHERE nivel=?"; // públicos y privados; filtra si quieres
        $rows = $this->select($sql, [$nivel]);
        return array_map(fn($r) => (int)$r['id'], $rows);
    }
}
