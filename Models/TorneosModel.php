<?php
class TorneosModel extends Mysql
{
    protected string $table = 'torneos';

    public function buscarPorId(int $id): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?", [$id]);
    }

    public function buscarPorSlug(string $slug): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE slug=?", [$slug]);
    }

    public function slugDisponible(string $slug, ?int $excludeId=null): bool {
        $sql="SELECT id FROM {$this->table} WHERE slug=?";
        $vals=[$slug];
        if ($excludeId) { $sql.=" AND id<>?"; $vals[]=$excludeId; }
        return $this->select_one($sql,$vals) ? false : true;
    }

    public function crear(array $d): ?int {
        $sql="INSERT INTO {$this->table}
              (titulo,slug,modalidad,inicio,fin,lugar,precio,cupo,resumen,descripcion,portada,bases_pdf,estado,form_activo,creado_por)
              VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['titulo'], $d['slug'], $d['modalidad'], $d['inicio'], $d['fin'] ?? null,
            $d['lugar'] ?? null, $d['precio'] ?? 0, $d['cupo'] ?? null,
            $d['resumen'] ?? null, $d['descripcion'] ?? null, $d['portada'] ?? null,
            $d['bases_pdf'] ?? null, $d['estado'] ?? 'publicado', (int)($d['form_activo'] ?? 1),
            $d['creado_por'] ?? null
        ]);
    }

    public function actualizar(int $id, array $d): int {
        $sql="UPDATE {$this->table} SET
              titulo=?, slug=?, modalidad=?, inicio=?, fin=?, lugar=?, precio=?, cupo=?,
              resumen=?, descripcion=?, portada=?, bases_pdf=?, estado=?, form_activo=?, updated_at=NOW()
              WHERE id=?";
        return $this->update($sql, [
            $d['titulo'], $d['slug'], $d['modalidad'], $d['inicio'], $d['fin'] ?? null,
            $d['lugar'] ?? null, $d['precio'] ?? 0, $d['cupo'] ?? null,
            $d['resumen'] ?? null, $d['descripcion'] ?? null, $d['portada'] ?? null,
            $d['bases_pdf'] ?? null, $d['estado'] ?? 'publicado', (int)($d['form_activo'] ?? 1),
            $id
        ]);
    }

    public function borrar(int $id): int {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }

    /* Listado ADMIN con filtros */
    public function listarAdmin(?string $estado, ?string $modalidad, int $page, int $per, ?string $q, string $orden): array {
        $page=max(1,$page); $per=max(1,$per); $off=($page-1)*$per;
        $allow=[
            'recientes'=>'created_at DESC',
            'fecha_asc'=>'inicio ASC',
            'fecha_desc'=>'inicio DESC',
            'titulo_asc'=>'titulo ASC',
            'titulo_desc'=>'titulo DESC'
        ];
        $orderBy=$allow[$orden] ?? $allow['recientes'];

        $where="FROM {$this->table} WHERE 1";
        $vals=[];
        if ($estado)    { $where.=" AND estado=?";    $vals[]=$estado; }
        if ($modalidad) { $where.=" AND modalidad=?"; $vals[]=$modalidad; }
        if ($q) {
            $where.=" AND (titulo LIKE ? OR lugar LIKE ?)";
            $vals[]='%'.$q.'%'; $vals[]='%'.$q.'%';
        }

        $items=$this->select("SELECT id,titulo,slug,modalidad,inicio,fin,lugar,precio,estado,form_activo,portada {$where} ORDER BY {$orderBy} LIMIT {$per} OFFSET {$off}", $vals);
        $row=$this->select_one("SELECT COUNT(*) c {$where}", $vals);
        $total=(int)($row['c']??0);
        return ['items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total,'total_pages'=>(int)ceil($total/$per)];
    }

    /* Listado PÚBLICO con filtros + paginación */
    public function listarPublico(?string $modalidad, int $page, int $per, ?string $q, string $orden, ?string $desde, ?string $hasta): array {
        $page=max(1,$page); $per=max(1,$per); $off=($page-1)*$per;
        $allow=['proximos'=>'inicio ASC','fecha_desc'=>'inicio DESC','titulo_asc'=>'titulo ASC','titulo_desc'=>'titulo DESC','recientes'=>'created_at DESC'];
        $orderBy=$allow[$orden] ?? $allow['proximos'];

        $where="FROM {$this->table} WHERE estado='publicado'";
        $vals=[];
        if ($modalidad) { $where.=" AND modalidad=?"; $vals[]=$modalidad; }
        if ($q) { $where.=" AND (titulo LIKE ? OR lugar LIKE ?)"; $vals[]='%'.$q.'%'; $vals[]='%'.$q.'%'; }
        if ($desde) { $where.=" AND inicio >= ?"; $vals[]=$desde.' 00:00:00'; }
        if ($hasta) { $where.=" AND inicio <= ?"; $vals[]=$hasta.' 23:59:59'; }

        $items=$this->select("SELECT id,titulo,slug,modalidad,inicio,fin,lugar,precio,portada,resumen,bases_pdf,form_activo {$where} ORDER BY {$orderBy} LIMIT {$per} OFFSET {$off}", $vals);
        $row=$this->select_one("SELECT COUNT(*) c {$where}", $vals);
        $total=(int)($row['c']??0);
        return ['items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total,'total_pages'=>(int)ceil($total/$per)];
    }

    public function proximosPublico(int $limit=3): array {
        $now = date('Y-m-d H:i:s');
        return $this->select("SELECT id,titulo,slug,modalidad,inicio,fin,lugar,precio,portada,resumen,bases_pdf,form_activo
                               FROM {$this->table}
                              WHERE estado='publicado' AND inicio>=?
                              ORDER BY inicio ASC LIMIT {$limit}", [$now]);
    }
}
