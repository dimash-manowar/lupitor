<?php
class EjercicioIntentoModel extends Mysql
{
    protected string $tabla = 'ejercicios_intentos';

    public function crear(array $d): ?int
    {
        $sql = "INSERT INTO {$this->tabla}
          (asignacion_id, alumno_id, pgn_enviado, correcto, movimientos, tiempo_seg, puntuacion, comentario)
          VALUES (?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['asignacion_id'], $d['alumno_id'], $d['pgn_enviado'] ?? null,
            (int)($d['correcto'] ?? 0), $d['movimientos'] ?? null,
            $d['tiempo_seg'] ?? null, $d['puntuacion'] ?? null, $d['comentario'] ?? null
        ]);
    }

    public function listarPorAsignacionYAlumno(int $asigId, int $uid): array
    {
        $sql = "SELECT * FROM {$this->tabla}
                WHERE asignacion_id=? AND alumno_id=?
                ORDER BY id DESC";
        return $this->select($sql, [$asigId, $uid]);
    }

    public function mejorPorAsignacionYAlumno(int $asigId, int $uid): ?array
    {
        $sql = "SELECT * FROM {$this->tabla}
                WHERE asignacion_id=? AND alumno_id=? AND correcto=1
                ORDER BY puntuacion DESC, tiempo_seg ASC, id ASC
                LIMIT 1";
        return $this->select_one($sql, [$asigId, $uid]);
    }

    public function contarPorAsignacionYAlumno(int $asigId, int $uid): int
    {
        $row = $this->select_one(
            "SELECT COUNT(*) c FROM {$this->tabla} WHERE asignacion_id=? AND alumno_id=?",
            [$asigId, $uid]
        );
        return (int)($row['c'] ?? 0);
    }
}
