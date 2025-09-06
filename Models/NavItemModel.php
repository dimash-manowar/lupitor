<?php
class NavItemModel extends Mysql
{
    private string $table = 'nav_items';

    /**
     * Devuelve los ítems del menú principal visibles y ordenados.
     */
    public function listarPrincipal(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE menu = 'principal' AND visible = 1 
                ORDER BY orden ASC";
        return $this->select($sql);
    }
    public function listarPorMenu(string $menu): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE menu = ? AND visible = 1
                ORDER BY orden ASC";
        return $this->select($sql, [$menu]);
    }
    public function menuTree(string $menu): array
    {
        $rows = $this->select(
            "SELECT id, menu, titulo, url, target, icono, parent_id, orden
               FROM {$this->table}
              WHERE menu=? AND visible=1
           ORDER BY COALESCE(parent_id,0) ASC, orden ASC, id ASC",
            [$menu]
        );

        // índice por id
        $byId = [];
        foreach ($rows as $r) {
            $r['children'] = [];
            $byId[(int)$r['id']] = $r;
        }

        // construir árbol
        $tree = [];
        foreach ($byId as $id => $node) {
            $pid = (int)($node['parent_id'] ?? 0);
            if ($pid && isset($byId[$pid])) {
                $byId[$pid]['children'][] = &$byId[$id];
            } else {
                $tree[] = &$byId[$id];
            }
        }
        return $tree;
    }
}
