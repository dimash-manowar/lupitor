<?php
class ClubSeccionModel extends Mysql
{
    protected string $table = 'club_secciones';

    public function listar(array $f, int $page, int $per): array {
        $page=max(1,$page); $per=max(1,$per); $off=($page-1)*$per;
        $where="FROM {$this->table} WHERE 1";
        $vals=[];
        if (!empty($f['estado'])) { $where.=" AND estado=?"; $vals[]=$f['estado']; }
        if (!empty($f['tipo']))   { $where.=" AND tipo=?";   $vals[]=$f['tipo']; }
        if (!empty($f['q'])) {
            $where.=" AND (titulo LIKE ? OR resumen LIKE ?)";
            $vals[]='%'.$f['q'].'%'; $vals[]='%'.$f['q'].'%';
        }
        $orderMap=[
            'recientes'=>"created_at DESC",
            'titulo_asc'=>"titulo ASC",
            'titulo_desc'=>"titulo DESC",
            'orden'=>"orden ASC, id ASC"
        ];
        $orderBy = $orderMap[$f['orden'] ?? 'orden'] ?? $orderMap['orden'];

        $items = $this->select("SELECT * {$where} ORDER BY {$orderBy} LIMIT {$per} OFFSET {$off}", $vals);
        $row = $this->select_one("SELECT COUNT(*) c {$where}", $vals);
        $total=(int)($row['c']??0);

        return ['items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total,'total_pages'=>(int)ceil($total/$per)];
    }

    public function listarPublico(?string $tipo=null): array {
        $sql="SELECT * FROM {$this->table} WHERE estado='publicado'";
        $vals=[];
        if ($tipo) { $sql.=" AND tipo=?"; $vals[]=$tipo; }
        $sql.=" ORDER BY orden ASC, id ASC";
        return $this->select($sql,$vals);
    }

    public function buscarPorId(int $id): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?",[$id]);
    }
    public function buscarPorSlug(string $slug): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE slug=?",[$slug]);
    }

    public function slugDisponible(string $slug, ?int $excludeId=null): bool {
        $sql="SELECT id FROM {$this->table} WHERE slug=?";
        $vals=[$slug];
        if ($excludeId) { $sql.=" AND id <> ?"; $vals[]=$excludeId; }
        return $this->select_one($sql,$vals) ? false : true;
    }

    public function crear(array $d): ?int {
        $sql="INSERT INTO {$this->table}(tipo,titulo,slug,resumen,cuerpo_html,portada,estado,orden)
              VALUES (?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['tipo'] ?? 'info',
            $d['titulo'] ?? '',
            $d['slug'] ?? '',
            $d['resumen'] ?? null,
            $d['cuerpo_html'] ?? null,
            $d['portada'] ?? null,
            $d['estado'] ?? 'publicado',
            (int)($d['orden'] ?? 0),
        ]);
    }

    public function actualizar(int $id, array $d): int {
        $sql="UPDATE {$this->table}
                SET tipo=?, titulo=?, slug=?, resumen=?, cuerpo_html=?, portada=?, estado=?, orden=?, updated_at=NOW()
              WHERE id=?";
        return $this->update($sql, [
            $d['tipo'] ?? 'info',
            $d['titulo'] ?? '',
            $d['slug'] ?? '',
            $d['resumen'] ?? null,
            $d['cuerpo_html'] ?? null,
            $d['portada'] ?? null,
            $d['estado'] ?? 'publicado',
            (int)($d['orden'] ?? 0),
            $id
        ]);
    }

    public function eliminar(int $id): int {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?",[$id]);
    }
}
