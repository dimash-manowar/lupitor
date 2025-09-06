<?php
class GaleriaModel extends Mysql
{
    protected string $albums = 'galeria_albums';
    protected string $media  = 'galeria_media';

    /* === ÁLBUMES === */
    public function listarAlbums(?bool $soloVisibles = false): array
    {
        $where = $soloVisibles ? "WHERE visible=1" : "";
        return $this->select("SELECT id,titulo,slug,descripcion,visible,orden,created_at FROM {$this->albums} {$where} ORDER BY orden ASC, id DESC");
    }
    public function albumPorId(int $id): ?array
    {
        return $this->select_one("SELECT * FROM {$this->albums} WHERE id=?", [$id]);
    }
    public function albumPorSlug(string $slug): ?array
    {
        return $this->select_one("SELECT * FROM {$this->albums} WHERE slug=?", [$slug]);
    }
    public function crearAlbum(array $d): ?int
    {
        return $this->insert("INSERT INTO {$this->albums}(titulo,slug,descripcion,visible,orden) VALUES (?,?,?,?,?)", [
            $d['titulo'],
            $d['slug'],
            $d['descripcion'] ?? null,
            (int)($d['visible'] ?? 1),
            (int)($d['orden'] ?? 1)
        ]);
    }
    public function actualizarAlbum(int $id, array $d): int
    {
        return $this->update("UPDATE {$this->albums} SET titulo=?, slug=?, descripcion=?, visible=?, orden=?, updated_at=NOW() WHERE id=?", [
            $d['titulo'],
            $d['slug'],
            $d['descripcion'] ?? null,
            (int)($d['visible'] ?? 1),
            (int)($d['orden'] ?? 1),
            $id
        ]);
    }
    public function eliminarAlbum(int $id): int
    {
        return $this->delete("DELETE FROM {$this->albums} WHERE id=?", [$id]);
    }

    /* === MEDIA === */
    public function listarMedia(?int $albumId = null, ?string $tipo = null, ?string $q = null, bool $soloVisibles = false): array
    {
        $where = "WHERE 1";
        $vals  = [];
        if ($albumId) {
            $where .= " AND m.album_id=?";
            $vals[] = $albumId;
        }
        if ($tipo) {
            $where .= " AND m.tipo=?";
            $vals[] = $tipo;
        }
        if ($soloVisibles) {
            $where .= " AND m.visible=1";
        }
        if ($q) {
            $where .= " AND (m.titulo LIKE ? OR m.descripcion LIKE ?)";
            $vals[] = "%$q%";
            $vals[] = "%$q%";
        }
        return $this->select("SELECT m.*, a.titulo AS album
                                FROM {$this->media} m
                           LEFT JOIN {$this->albums} a ON a.id=m.album_id
                               {$where}
                            ORDER BY m.orden ASC, m.id DESC", $vals);
    }
    public function mediaPorId(int $id): ?array
    {
        return $this->select_one("SELECT * FROM {$this->media} WHERE id=?", [$id]);
    }
    
    public function eliminarMedia(int $id): int
    {
        return $this->delete("DELETE FROM {$this->media} WHERE id=?", [$id]);
    }
    public function ultimosPublicos(int $limit = 8, ?int $albumId = null): array
    {
        $limit = max(1, min(50, $limit));
        $vals = [];
        $where = "WHERE m.visible=1 AND (a.visible=1 OR m.album_id IS NULL)";
        if ($albumId) {
            $where .= " AND m.album_id=?";
            $vals[] = $albumId;
        }

        return $this->select(
            "SELECT
            m.id, m.album_id, m.tipo, m.titulo, m.archivo_path, m.youtube_id, m.video_path, m.created_at,
            a.titulo AS album, a.slug AS album_slug
         FROM {$this->media} m
         LEFT JOIN {$this->albums} a ON a.id = m.album_id
         {$where}
         ORDER BY m.created_at DESC
         LIMIT {$limit}",
            $vals
        );
    }
    public function contarVisibles(): array
    {
        $row = $this->select_one(
            "SELECT
         SUM(CASE WHEN tipo='imagen' THEN 1 ELSE 0 END) AS fotos,
         SUM(CASE WHEN tipo='video'  THEN 1 ELSE 0 END) AS videos
       FROM {$this->media}
       WHERE visible=1"
        );
        return ['fotos' => (int)($row['fotos'] ?? 0), 'videos' => (int)($row['videos'] ?? 0)];
    }
    public function crearMedia(array $d): ?int
    {
        $sql = "INSERT INTO {$this->media}
          (album_id,tipo,titulo,alumno_nombre,descripcion,archivo_path,thumb_path,youtube_id,vimeo_id,video_path,torneo_id,visible,orden)
          VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['album_id'] ?? null,
            $d['tipo'],
            $d['titulo'],
            $d['alumno_nombre'] ?? null,
            $d['descripcion'] ?? null,
            $d['archivo_path'] ?? null,
            $d['thumb_path'] ?? null,
            $d['youtube_id'] ?? null,
            $d['vimeo_id'] ?? null,
            $d['video_path'] ?? null,
            $d['torneo_id'] ?? null,
            (int)($d['visible'] ?? 1),
            (int)($d['orden'] ?? 1)
        ]);
    }

    public function actualizarMedia(int $id, array $d): int
    {
        $sql = "UPDATE {$this->media} SET
            album_id=?, tipo=?, titulo=?, alumno_nombre=?, descripcion=?,
            archivo_path=?, thumb_path=?, youtube_id=?, vimeo_id=?, video_path=?,
            torneo_id=?, visible=?, orden=?, updated_at=NOW()
          WHERE id=?";
        return $this->update($sql, [
            $d['album_id'] ?? null,
            $d['tipo'],
            $d['titulo'],
            $d['alumno_nombre'] ?? null,
            $d['descripcion'] ?? null,
            $d['archivo_path'] ?? null,
            $d['thumb_path'] ?? null,
            $d['youtube_id'] ?? null,
            $d['vimeo_id'] ?? null,
            $d['video_path'] ?? null,
            $d['torneo_id'] ?? null,
            (int)($d['visible'] ?? 1),
            (int)($d['orden'] ?? 1),
            $id
        ]);
    }
    public function fotosParaCarousel(int $limit = 10, ?string $albumSlug = null): array
    {
        $limit = max(1, min(50, $limit));
        $vals = [];
        $extra = '';
        if ($albumSlug) {
            $extra = " AND a.slug=?";
            $vals[] = $albumSlug;
        }
        return $this->select(
            "SELECT m.id, m.titulo, m.alumno_nombre, m.archivo_path, m.created_at,
                a.titulo AS album, a.slug AS album_slug
           FROM {$this->media} m
           LEFT JOIN {$this->albums} a ON a.id=m.album_id
          WHERE m.visible=1 AND m.tipo='imagen' AND (a.visible=1 OR m.album_id IS NULL) {$extra}
          ORDER BY m.created_at DESC
          LIMIT {$limit}",
            $vals
        );
    }
}
