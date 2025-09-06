<?php
class ClubPersonaModel extends Mysql
{
    protected string $table='club_personas';

    public function listar(array $f, int $page, int $per): array {
        $page=max(1,$page); $per=max(1,$per); $off=($page-1)*$per;
        $where="FROM {$this->table} WHERE 1";
        $vals=[];
        if (!empty($f['tipo']))    { $where.=" AND tipo=?"; $vals[]=$f['tipo']; }
        if (isset($f['visible']) && $f['visible']!=='') { $where.=" AND visible=?"; $vals[]=(int)$f['visible']; }
        if (!empty($f['q'])) {
            $where.=" AND (nombre LIKE ? OR apellidos LIKE ?)";
            $vals[]='%'.$f['q'].'%'; $vals[]='%'.$f['q'].'%';
        }
        $orderMap=[
            'orden'=>"orden ASC, id ASC",
            'nombre_asc'=>"apellidos ASC, nombre ASC",
            'nombre_desc'=>"apellidos DESC, nombre DESC",
            'recientes'=>"created_at DESC"
        ];
        $orderBy=$orderMap[$f['orden'] ?? 'orden'] ?? $orderMap['orden'];

        $items=$this->select("SELECT * {$where} ORDER BY {$orderBy} LIMIT {$per} OFFSET {$off}",$vals);
        $row=$this->select_one("SELECT COUNT(*) c {$where}",$vals);
        $total=(int)($row['c']??0);
        return ['items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total,'total_pages'=>(int)ceil($total/$per)];
    }

    public function obtener(int $id): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?",[$id]);
    }

    public function crear(array $d): ?int {
        $sql="INSERT INTO {$this->table}(tipo,nombre,apellidos,foto,bio,email,elo,visible,orden)
              VALUES (?,?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['tipo'] ?? 'socio',
            $d['nombre'] ?? '',
            $d['apellidos'] ?? null,
            $d['foto'] ?? null,
            $d['bio'] ?? null,
            $d['email'] ?? null,
            $d['elo'] ?? null,
            isset($d['visible']) ? (int)$d['visible'] : 1,
            (int)($d['orden'] ?? 0),
        ]);
    }

    public function actualizar(int $id, array $d): int {
        $sql="UPDATE {$this->table}
              SET tipo=?, nombre=?, apellidos=?, foto=?, bio=?, email=?, elo=?, visible=?, orden=?, updated_at=NOW()
              WHERE id=?";
        return $this->update($sql, [
            $d['tipo'] ?? 'socio',
            $d['nombre'] ?? '',
            $d['apellidos'] ?? null,
            $d['foto'] ?? null,
            $d['bio'] ?? null,
            $d['email'] ?? null,
            $d['elo'] ?? null,
            isset($d['visible']) ? (int)$d['visible'] : 1,
            (int)($d['orden'] ?? 0),
            $id
        ]);
    }

    public function eliminar(int $id): int {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?",[$id]);
    }
}
