<?php
class NoticiaMediaModel extends Mysql
{
    private string $table = 'noticias_medios'; // tabla puente

    public function adjuntar(int $noticiaId, int $medioId, string $tipo = 'imagen', int $orden = 0): ?int
    {
        $sql = "INSERT IGNORE INTO {$this->table} (noticia_id, medio_id, tipo, orden) VALUES (?,?,?,?)";
        return $this->insert($sql, [$noticiaId, $medioId, $tipo, $orden]);
    }    

    public function setOrden(int $noticiaId, int $medioId, int $orden): int
    {
        $sql = "UPDATE {$this->table} SET orden=? WHERE noticia_id=? AND medio_id=?";
        return $this->update($sql, [$orden, $noticiaId, $medioId]);
    }

    /** Lista la galería de una noticia con datos del medio */
    public function listarPorNoticia(int $noticiaId): array
    {
        $sql = "SELECT nm.medio_id, nm.tipo, nm.orden, m.filename, m.path, m.mime, m.width, m.height, m.size, m.alt_text
                  FROM {$this->table} nm
                  JOIN medios m ON m.id = nm.medio_id
                 WHERE nm.noticia_id=?
                 ORDER BY nm.orden ASC, nm.medio_id ASC";
        return $this->select($sql, [$noticiaId]);
    }

    /** (opcional) compacta orden 1..n */
    public function compactarOrden(int $noticiaId): void
    {
        $items = $this->select("SELECT medio_id FROM {$this->table} WHERE noticia_id=? ORDER BY orden, medio_id", [$noticiaId]);
        $pos = 1;
        foreach ($items as $it) {
            $this->setOrden($noticiaId, (int)$it['medio_id'], $pos++);
        }
    }
    public function quitar(int $noticiaId, int $medioId): int
    {
        $sql = "DELETE FROM {$this->table} WHERE noticia_id=? AND medio_id=?";
        return $this->delete($sql, [$noticiaId, $medioId]);
    }

    public function contarVinculos(int $medioId): int
    {
        $row = $this->select_one("SELECT COUNT(*) AS c FROM {$this->table} WHERE medio_id=?", [$medioId]);
        return (int)($row['c'] ?? 0);
    }
}
