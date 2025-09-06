<?php
class ClubMediaModel extends Mysql
{
    protected string $table='club_media';

    public function listar(?int $seccionId, int $page, int $per): array {
        $page=max(1,$page); $per=max(1,$per); $off=($page-1)*$per;
        $where="FROM {$this->table} WHERE 1";
        $vals=[];
        if ($seccionId) { $where.=" AND seccion_id=?"; $vals[]=$seccionId; }

        $items=$this->select("SELECT * {$where} ORDER BY orden ASC, id ASC LIMIT {$per} OFFSET {$off}",$vals);
        $row=$this->select_one("SELECT COUNT(*) c {$where}",$vals);
        $total=(int)($row['c']??0);
        return ['items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total,'total_pages'=>(int)ceil($total/$per)];
    }

    public function crear(array $d): ?int {
        $sql="INSERT INTO {$this->table}(seccion_id,tipo,path,mime,titulo,alt_text,visible,orden)
              VALUES (?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['seccion_id'] ?? null,
            $d['tipo'] ?? 'foto',
            $d['path'] ?? '',
            $d['mime'] ?? null,
            $d['titulo'] ?? null,
            $d['alt_text'] ?? null,
            isset($d['visible']) ? (int)$d['visible'] : 1,
            (int)($d['orden'] ?? 0),
        ]);
    }

    public function eliminar(int $id): int {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?",[$id]);
    }

    public function obtener(int $id): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?",[$id]);
    }
}
