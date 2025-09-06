<?php
trait Paginable
{
    /**
     * Pagina una tabla con opciones de búsqueda y orden.
     * @param string $table Nombre de la tabla
     * @param int $page Página actual (>=1)
     * @param int $perPage Registros por página
     * @param array $columns Columnas a seleccionar (['*'] por defecto)
     * @param array $whereCols Columnas donde aplicar búsqueda LIKE (['nombre','email'])
     * @param string|null $search Texto a buscar (opcional)
     * @param string|null $orderBy Columna para ordenar (debe estar en $orderWhitelist)
     * @param string $orderDir 'ASC'|'DESC'
     * @param array $orderWhitelist Lista blanca de columnas ordenables
     * @param array $extraWhere SQL adicional (ej: ['rol = ?' => ['admin']])
     * @return array [items=>[], meta=>[]]
     */
    protected function paginate(
        string $table,
        int $page = 1,
        int $perPage = 10,
        array $columns = ['*'],
        array $whereCols = [],
        ?string $search = null,
        ?string $orderBy = null,
        string $orderDir = 'ASC',
        array $orderWhitelist = [],
        array $extraWhere = []
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));


        // WHERE dinámico
        $wheres = [];
        $params = [];


        if ($search !== null && $search !== '' && !empty($whereCols)) {
            $likeParts = [];
            foreach ($whereCols as $col) {
                // columnas simples seguras (sin backticks dinámicos)
                $likeParts[] = "$col LIKE ?";
                $params[] = "%$search%";
            }
            $wheres[] = '(' . implode(' OR ', $likeParts) . ')';
        }
        // extra where: ['rol = ?' => ['admin'], 'activo = 1' => []]
        foreach ($extraWhere as $cond => $p) {
            $wheres[] = $cond;
            if (!empty($p)) {
                foreach ($p as $v) $params[] = $v;
            }
        }
        $whereSql = $wheres ? (' WHERE ' . implode(' AND ', $wheres)) : '';


        // COUNT(*)
        $sqlCount = "SELECT COUNT(*) AS total FROM $table$whereSql";
        $row = $this->select_one($sqlCount, $params);
        $total = (int)($row['total'] ?? 0);
        $totalPages = (int)ceil($total / $perPage);
        if ($totalPages === 0) {
            $totalPages = 1;
        }
        if ($page > $totalPages) {
            $page = $totalPages;
        }


        // ORDER BY seguro
        $orderSql = '';
        if ($orderBy && (empty($orderWhitelist) || in_array($orderBy, $orderWhitelist, true))) {
            $dir = strtoupper($orderDir) === 'DESC' ? 'DESC' : 'ASC';
            $orderSql = " ORDER BY $orderBy $dir";
        }


        // LIMIT/OFFSET
        $offset = ($page - 1) * $perPage;
        $cols = implode(',', $columns);
        $sql = "SELECT $cols FROM $table$whereSql$orderSql LIMIT $perPage OFFSET $offset";
        $items = $this->select($sql, $params);
    }
}
