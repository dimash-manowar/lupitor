<?php
class EventoModel extends Mysql
{
    protected string $table = 'eventos';

    public function obtener(int $id): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=?", [$id]);
    }

    public function listarProximosPublico(int $limit = 1): array {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT id,titulo,tipo,inicio,fin,lugar,portada,resumen,bases_pdf
                  FROM {$this->table}
                 WHERE estado='publicado' AND inicio >= ?
                 ORDER BY inicio ASC
                 LIMIT ?";
        return $this->select($sql, [$now, $limit]);
    }

    /**
     * Listado público con filtros (para /Eventos/index)
     * $orden: proximos | fecha_desc | titulo_asc | titulo_desc | recientes
     */
    public function listarPublico(?string $modalidad, int $page, int $per, ?string $q, string $orden, ?string $desde, ?string $hasta): array
    {
        $page=max(1,$page); $per=max(1,$per); $off=($page-1)*$per;
        $allow = [
            'proximos'    => 'inicio ASC',
            'fecha_desc'  => 'inicio DESC',
            'titulo_asc'  => 'titulo ASC',
            'titulo_desc' => 'titulo DESC',
            'recientes'   => 'created_at DESC'
        ];
        $orderBy = $allow[$orden] ?? $allow['proximos'];

        $where="FROM {$this->table} WHERE estado='publicado'";
        $vals=[];

        if ($modalidad) { // si prefieres filtrar por tipo
            $where .= " AND tipo = ?";
            $vals[] = $modalidad; // valores: 'quedada','torneo','clase','otro'
        }
        if ($q) {
            $where .= " AND (titulo LIKE ? OR lugar LIKE ? OR descripcion LIKE ?)";
            $vals[]='%'.$q.'%'; $vals[]='%'.$q.'%'; $vals[]='%'.$q.'%';
        }
        if ($desde) { $where .= " AND inicio >= ?"; $vals[] = $desde . ' 00:00:00'; }
        if ($hasta) { $where .= " AND inicio <= ?"; $vals[] = $hasta . ' 23:59:59'; }

        $items = $this->select(
            "SELECT id,titulo,tipo,inicio,fin,lugar,portada,resumen,bases_pdf {$where} ORDER BY {$orderBy} LIMIT {$per} OFFSET {$off}",
            $vals
        );
        $row = $this->select_one("SELECT COUNT(*) c {$where}", $vals);
        $total = (int)($row['c'] ?? 0);

        return ['items'=>$items,'page'=>$page,'per'=>$per,'total'=>$total,'total_pages'=>(int)ceil($total/$per)];
    }
}
