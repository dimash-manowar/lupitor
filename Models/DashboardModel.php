<?php
class DashboardModel extends Mysql
{
    /** Cuenta filas de una tabla (si existe); si no existe, devuelve 0 */
    public function countIfExists(string $table): int
    {
        try {
            // Comprobar existencia de tabla
            $chk = $this->select_one("SHOW TABLES LIKE ?", [$table]);
            if (!$chk) return 0;
            $row = $this->select_one("SELECT COUNT(*) AS c FROM `$table`");
            return (int)($row['c'] ?? 0);
        } catch (Throwable $e) {
            return 0;
        }
    }


    /** Últimas noticias (id, titulo, fecha, imagen) */
    public function ultimasNoticias(int $limit = 5): array
    {
        // Si no existe la tabla, lista vacía
        $chk = $this->select_one("SHOW TABLES LIKE 'noticias'");
        if (!$chk) return [];
        $sql = "SELECT id, titulo, created_at  FROM noticias ORDER BY created_at DESC, id DESC LIMIT ?";
        // Para LIMIT con PDO y emulación off, bindeamos como int explícitamente
        $stmt = $this->conect->prepare($sql);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
