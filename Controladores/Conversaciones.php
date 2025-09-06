// Controladores/Conversaciones.php (muy simple)
<?php
class Conversaciones extends Controlador
{
    public function unreadCount()
    {
        requireLogin();
        header('Content-Type: application/json; charset=utf-8');
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $row = (new ConversacionModel())->select_one(
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
        echo json_encode(['ok'=>true,'count'=>(int)($row['c'] ?? 0)]);
    }
}
