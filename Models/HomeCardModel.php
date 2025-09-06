<?php
class HomeCardModel extends Mysql
{
    private string $table = 'home_cards';

    public function listar(int $limit = 0): array {
        $sql = "SELECT id,titulo,descripcion,icono,color_fondo,color_texto,imagen,boton_texto,destino,orden,visible
                FROM {$this->table} ORDER BY orden ASC, id ASC";
        if ($limit > 0) { $sql .= " LIMIT " . (int)$limit; }
        return $this->select($sql);
    }

    public function listarVisibles(int $limit = 3): array {
        $sql = "SELECT id,titulo,descripcion,icono,color_fondo,color_texto,imagen,boton_texto,destino,orden,visible
                FROM {$this->table} WHERE visible=1 ORDER BY orden ASC, id ASC LIMIT ?";
        return $this->select($sql, [(int)$limit]);
    }

    public function find(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE id=?";
        return $this->select_one($sql, [$id]);
    }

    public function crear(array $d): ?int {
        $sql = "INSERT INTO {$this->table}
                (titulo,descripcion,icono,color_fondo,color_texto,imagen,boton_texto,destino,orden,visible)
                VALUES (?,?,?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['titulo'], $d['descripcion'], $d['icono'] ?? null,
            $d['color_fondo'] ?? '#222', $d['color_texto'] ?? '#fff',
            $d['imagen'] ?? null, $d['boton_texto'] ?? 'Ver más',
            $d['destino'] ?? null, (int)($d['orden'] ?? 0), (int)($d['visible'] ?? 1),
        ]);
    }

    public function actualizar(int $id, array $d): int {
        $sql = "UPDATE {$this->table}
                SET titulo=?, descripcion=?, icono=?, color_fondo=?, color_texto=?, imagen=?, boton_texto=?, destino=?, orden=?, visible=?
                WHERE id=?";
        return $this->update($sql, [
            $d['titulo'], $d['descripcion'], $d['icono'] ?? null,
            $d['color_fondo'] ?? '#222', $d['color_texto'] ?? '#fff',
            $d['imagen'] ?? null, $d['boton_texto'] ?? 'Ver más',
            $d['destino'] ?? null, (int)($d['orden'] ?? 0), (int)($d['visible'] ?? 1),
            $id
        ]);
    }

    public function borrar(int $id): int {
        $sql = "DELETE FROM {$this->table} WHERE id=?";
        return $this->delete($sql, [$id]);
    }

    public function setVisible(int $id, int $visible): int {
        $sql = "UPDATE {$this->table} SET visible=? WHERE id=?";
        return $this->update($sql, [$visible ? 1 : 0, $id]);
    }

    public function setOrden(int $id, int $orden): int {
        $sql = "UPDATE {$this->table} SET orden=? WHERE id=?";
        return $this->update($sql, [(int)$orden, $id]);
    }
}
