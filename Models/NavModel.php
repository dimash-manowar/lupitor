<?php
class NavModel extends Mysql
{
    protected string $tabla = 'nav_items';

    /**
     * Lista nav items por menú (ej: 'usuario' | 'admin') y añade 'badge' si corresponde.
     */
    public function listarPorMenu(string $menu, ?int $usuarioId = null): array
    {
        $items = $this->select(
            "SELECT id, menu, titulo, destino, url, target, icono, parent_id, orden, visible
             FROM {$this->tabla}
             WHERE visible = 1 AND menu = ?
             ORDER BY COALESCE(parent_id,0), orden ASC, id ASC",
            [$menu]
        );

        if (!$usuarioId) return $items;

        // Calcula contadores una sola vez
        $noti = $this->contarNotificacionesNoLeidas($usuarioId);
        $msgs = $this->contarMensajesNoLeidos($usuarioId);

        foreach ($items as &$it) {
            $t = mb_strtolower($it['titulo'] ?? '', 'UTF-8');
            $u = mb_strtolower($it['url']    ?? '', 'UTF-8');

            // Heurística sin columna extra: si el título o la url apuntan
            // a mensajes / notificaciones, añadimos badge.
            if (strpos($u, 'mensajes') === 0 || $t === 'mensajes') {
                $it['badge'] = $msgs;
            } elseif (strpos($u, 'notificaciones') === 0 || $t === 'notificaciones') {
                $it['badge'] = $noti;
            } else {
                $it['badge'] = 0;
            }
        }
        unset($it);
        return $items;
    }

    private function contarNotificacionesNoLeidas(int $uid): int
    {
        $row = $this->select_one(
            "SELECT COUNT(*) AS c FROM notificaciones WHERE usuario_id=? AND leida_en IS NULL",
            [$uid]
        );
        return (int)($row['c'] ?? 0);
    }

    private function contarMensajesNoLeidos(int $uid): int
    {
        $row = $this->select_one(
            "SELECT
               SUM(CASE
                     WHEN usuario_a_id = ? THEN no_leidos_a
                     WHEN usuario_b_id = ? THEN no_leidos_b
                     ELSE 0
                   END) AS c
             FROM conversaciones
             WHERE usuario_a_id = ? OR usuario_b_id = ?",
            [$uid, $uid, $uid, $uid]
        );
        return (int)($row['c'] ?? 0);
    }
}
