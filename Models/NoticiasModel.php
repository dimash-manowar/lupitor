<?php
class NoticiasModel extends Mysql
{
    private string $table = 'noticias';


    // ------- PUBLICO -------
    public function contarPublicas(?string $categoria = null, ?string $q = null): int
    {
        $sql = "SELECT COUNT(*) c FROM {$this->table} WHERE estado='publicado'";
        $vals = [];
        if ($categoria) {
            $sql .= " AND categoria=?";
            $vals[] = $categoria;
        }
        if ($q) {
            $sql .= " AND (titulo LIKE ? OR resumen LIKE ?)";
            $vals[] = "%$q%";
            $vals[] = "%$q%";
        }
        $row = $this->select_one($sql, $vals);
        return (int)($row['c'] ?? 0);
    }


    public function buscarPorSlug(string $slug): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug=? AND estado='publicado' LIMIT 1";
        return $this->select_one($sql, [$slug]);
    }

    /** Crea una noticia y devuelve el ID insertado (o null si falla). */
    public function crear(array $d): ?int
    {
        $sql = "INSERT INTO {$this->table}
                (titulo, slug, categoria, resumen, contenido, portada, autor_id, estado, publicado_at, created_at, updated_at)
                VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW())";
        return $this->insert($sql, [
            $d['titulo']        ?? null,
            $d['slug']          ?? null,
            $d['categoria']     ?? null,
            $d['resumen']       ?? null,
            $d['contenido']     ?? null,
            $d['portada']       ?? null,
            $d['autor_id']      ?? null,
            $d['estado']        ?? 'borrador',
            $d['publicado_at']  ?? null,
        ]);
    }

    /** Actualiza una noticia; devuelve filas afectadas (0 si sin cambios). */
    public function actualizar(int $id, array $d): int
    {
        $sql = "UPDATE {$this->table}
                   SET titulo=?,
                       slug=?,
                       categoria=?,
                       resumen=?,
                       contenido=?,
                       portada=?,
                       estado=?,
                       publicado_at=?,
                       updated_at=NOW()
                 WHERE id=?";
        return $this->update($sql, [
            $d['titulo']        ?? null,
            $d['slug']          ?? null,
            $d['categoria']     ?? null,
            $d['resumen']       ?? null,
            $d['contenido']     ?? null,
            $d['portada']       ?? null,
            $d['estado']        ?? 'borrador',
            $d['publicado_at']  ?? null,
            $id,
        ]);
    }

    /** Obtiene una noticia por ID (o null si no existe). */
    public function buscarPorId(int $id): ?array
    {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?", [$id]);
    }

    /** Devuelve true si el slug no está usado (opcionalmente excluyendo un id). */
    public function slugDisponible(string $slug, ?int $exceptId = null): bool
    {
        $sql  = "SELECT id FROM {$this->table} WHERE slug = ?";
        $vals = [$slug];
        if ($exceptId) {
            $sql .= " AND id <> ?";
            $vals[] = (int)$exceptId;
        }
        return $this->select_one($sql, $vals) === null;
    }

    /**
     * Listado para ADMIN con filtros + paginación.
     */
    public function listarAdmin(?string $estado, ?string $categoria, int $page, int $per, ?string $q, string $orden): array
    {
        $page   = max(1, (int)$page);
        $per    = max(1, (int)$per);
        $offset = ($page - 1) * $per;

        $allowOrden = [
            'recientes'   => 'created_at DESC',
            'pub_desc'    => 'publicado_at DESC',
            'pub_asc'     => 'publicado_at ASC',
            'titulo_asc'  => 'titulo ASC',
            'titulo_desc' => 'titulo DESC',
        ];
        $orderBy = $allowOrden[$orden] ?? $allowOrden['recientes'];

        $sql = "SELECT id, titulo, slug, categoria, resumen, portada, estado, publicado_at, created_at, updated_at
                FROM {$this->table}
                WHERE 1";
        $vals = [];

        if ($estado) {
            $sql .= " AND estado=?";
            $vals[] = $estado;
        }
        if ($categoria) {
            $sql .= " AND categoria=?";
            $vals[] = $categoria;
        }
        if ($q) {
            $sql .= " AND (titulo LIKE ? OR resumen LIKE ?)";
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
        }

        $sql .= " ORDER BY $orderBy LIMIT $per OFFSET $offset";
        $items = $this->select($sql, $vals);

        $total = $this->contarAdmin($estado, $categoria, $q);

        return [
            'items'       => $items,
            'page'        => $page,
            'per'         => $per,
            'total'       => $total,
            'total_pages' => (int)ceil($total / $per),
        ];
    }

    /**
     * Total para ADMIN (mismos filtros que listarAdmin).
     */
    public function contarAdmin(?string $estado, ?string $categoria, ?string $q): int
    {
        $sql = "SELECT COUNT(*) AS c FROM {$this->table} WHERE 1";
        $vals = [];

        if ($estado) {
            $sql .= " AND estado=?";
            $vals[] = $estado;
        }
        if ($categoria) {
            $sql .= " AND categoria=?";
            $vals[] = $categoria;
        }
        if ($q) {
            $sql .= " AND (titulo LIKE ? OR resumen LIKE ?)";
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
        }

        $row = $this->select_one($sql, $vals);
        return (int)($row['c'] ?? 0);
    }

    /**
     * Listado público (solo publicadas) con filtros básicos.
     * $categoria puede ser null; $q búsqueda; $orden: 'recientes' | 'titulo_asc' | 'titulo_desc' | 'pub_asc' | 'pub_desc'
     */
    public function listarPublico(?string $categoria, int $page, int $per, ?string $q, string $orden): array
    {
        $page   = max(1, (int)$page);
        $per    = max(1, (int)$per);
        $offset = ($page - 1) * $per;

        $allowOrden = [
            'recientes'   => 'publicado_at DESC',
            'pub_desc'    => 'publicado_at DESC',
            'pub_asc'     => 'publicado_at ASC',
            'titulo_asc'  => 'titulo ASC',
            'titulo_desc' => 'titulo DESC',
        ];
        $orderBy = $allowOrden[$orden] ?? $allowOrden['recientes'];

        $sql = "SELECT id, titulo, slug, categoria, resumen, portada, publicado_at
                FROM {$this->table}
                WHERE estado='publicado' AND publicado_at IS NOT NULL AND publicado_at <= NOW()";
        $vals = [];

        if ($categoria) {
            $sql .= " AND categoria=?";
            $vals[] = $categoria;
        }
        if ($q) {
            $sql .= " AND (titulo LIKE ? OR resumen LIKE ?)";
            $vals[] = '%' . $q . '%';
            $vals[] = '%' . $q . '%';
        }

        $sql .= " ORDER BY $orderBy LIMIT $per OFFSET $offset";
        $items = $this->select($sql, $vals);

        // total público
        $sqlCount = "SELECT COUNT(*) AS c
                     FROM {$this->table}
                     WHERE estado='publicado' AND publicado_at IS NOT NULL AND publicado_at <= NOW()";
        $valsCount = [];
        if ($categoria) {
            $sqlCount .= " AND categoria=?";
            $valsCount[] = $categoria;
        }
        if ($q) {
            $sqlCount .= " AND (titulo LIKE ? OR resumen LIKE ?)";
            $valsCount[] = '%' . $q . '%';
            $valsCount[] = '%' . $q . '%';
        }
        $row = $this->select_one($sqlCount, $valsCount);
        $total = (int)($row['c'] ?? 0);

        return [
            'items'       => $items,
            'page'        => $page,
            'per'         => $per,
            'total'       => $total,
            'total_pages' => (int)ceil($total / $per),
        ];
    }
    public function borrar(int $id): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }
}
