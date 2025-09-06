<?php
class MedioModel extends Mysql
{
    private string $table = 'medios';
    public function crear(array $d): ?int
    {
        $sql = "INSERT INTO {$this->table}
                (filename, path, mime, width, height, size, alt_text, subido_por, created_at, updated_at)
                VALUES (?,?,?,?,?,?,?, ?, NOW(), NOW())";
        return $this->insert($sql, [
            $d['filename'] ?? null,
            $d['path']     ?? null,
            $d['mime']     ?? null,
            $d['width']    ?? null,
            $d['height']   ?? null,
            $d['size']     ?? null,
            $d['alt_text'] ?? null,
            $d['subido_por'] ?? null,
        ]);
    }


    public function obtener(int $id): ?array
    {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?", [$id]);
    }

    public function borrarUno(int $id): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }
}
