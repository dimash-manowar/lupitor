<?php
class ConversacionModel extends Mysql
{
  protected string $tabla = 'conversaciones';

  /** Devuelve el id de la conversación (crea si no existe), ordenando el par (A=min, B=max). */
  public function obtenerOCrear(int $u1, int $u2): int
  {
    $a = min($u1, $u2);
    $b = max($u1, $u2);

    $row = $this->select_one(
      "SELECT id FROM {$this->tabla} WHERE usuario_a_id = ? AND usuario_b_id = ? LIMIT 1",
      [$a, $b]
    );
    if ($row && !empty($row['id'])) return (int)$row['id'];

    $id = $this->insert(
      "INSERT INTO {$this->tabla}
             (usuario_a_id, usuario_b_id, creado_el, fecha_ultimo_mensaje, ultimo_remitente_id, no_leidos_a, no_leidos_b)
             VALUES (?, ?, NOW(), NOW(), NULL, 0, 0)",
      [$a, $b]
    );
    return (int)$id;
  }

  /** Pone a 0 el contador de no leídos del usuario dado. */
  public function marcarLeido(int $conversacionId, int $usuarioId): int
  {
    $sql = "UPDATE {$this->tabla}
                SET no_leidos_a = CASE WHEN usuario_a_id = ? THEN 0 ELSE no_leidos_a END,
                    no_leidos_b = CASE WHEN usuario_b_id = ? THEN 0 ELSE no_leidos_b END
                WHERE id = ?";
    return $this->update($sql, [$usuarioId, $usuarioId, $conversacionId]);
  }

  /**
   * Tras nuevo mensaje: fecha_ultimo_mensaje=NOW(), ultimo_remitente_id,
   * e incrementa el no leído del receptor (el opuesto al remitente).
   */
  public function tocarTrasMensaje(int $conversacionId, int $remitenteId): int
  {
    $sql = "UPDATE {$this->tabla}
                SET fecha_ultimo_mensaje = NOW(),
                    ultimo_remitente_id = ?,
                    no_leidos_a = CASE WHEN usuario_b_id = ? THEN no_leidos_a + 1 ELSE no_leidos_a END,
                    no_leidos_b = CASE WHEN usuario_a_id = ? THEN no_leidos_b + 1 ELSE no_leidos_b END
                WHERE id = ?";
    return $this->update($sql, [$remitenteId, $remitenteId, $remitenteId, $conversacionId]);
  }

  /** Recalcula última actividad y último remitente (útil tras borrados). */
  public function recomputarMeta(int $conversacionId): void
  {
    $sql = "
      UPDATE {$this->tabla} c
      LEFT JOIN (
        SELECT m.conversacion_id, MAX(m.creado_en) AS last_at
        FROM mensajes m
        WHERE m.conversacion_id = ?
      ) t ON t.conversacion_id = c.id
      LEFT JOIN (
        SELECT m1.conversacion_id, m1.remitente_id
        FROM mensajes m1
        WHERE m1.conversacion_id = ?
        ORDER BY m1.creado_en DESC, m1.id DESC
        LIMIT 1
      ) u ON u.conversacion_id = c.id
      SET c.fecha_ultimo_mensaje = COALESCE(t.last_at, c.creado_el),
          c.ultimo_remitente_id  = u.remitente_id
      WHERE c.id = ?";
    $this->update($sql, [$conversacionId, $conversacionId, $conversacionId]);
  }



  /** Listado de conversaciones del usuario, con datos del otro participante. */
  public function listarPorUsuario(int $uid, int $page = 1, int $per = 20): array
  {
    $uid  = (int)$uid;
    $page = max(1, (int)$page);
    $per  = max(1, min(100, (int)$per));
    $off  = ($page - 1) * $per;

    $sql = "
          SELECT
            c.*,
            CASE WHEN c.usuario_a_id = ? THEN c.usuario_b_id ELSE c.usuario_a_id END AS otro_id,
            u.nombre   AS otro_nombre,
            u.email    AS otro_email,
            u.foto_url AS otro_foto
          FROM {$this->tabla} c
          JOIN usuarios u
            ON u.id = (CASE WHEN c.usuario_a_id = ? THEN c.usuario_b_id ELSE c.usuario_a_id END)
          WHERE c.usuario_a_id = ? OR c.usuario_b_id = ?
          ORDER BY c.fecha_ultimo_mensaje DESC
          LIMIT {$per} OFFSET {$off}";
    return $this->select($sql, [$uid, $uid, $uid, $uid]);
  }
}
