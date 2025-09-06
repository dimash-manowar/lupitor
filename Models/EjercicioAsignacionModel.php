<?php
// Models/EjercicioAsignacionModel.php
class EjercicioAsignacionModel extends Mysql
{
    protected string $tabla = 'ejercicios_asignaciones';

    /** Crea UNA asignación. Devuelve id o null. */
    public function crear(array $d): ?int
    {
        $sql = "INSERT INTO {$this->tabla}
          (ejercicio_id, profesor_id, destinatario_tipo, destinatario_id, fecha_limite, intentos_max, obligatorio, estado)
          VALUES (?,?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            (int)$d['ejercicio_id'],
            (int)$d['profesor_id'],
            $d['destinatario_tipo'] ?? 'usuario',     // usuario | grupo | nivel
            (int)$d['destinatario_id'],
            $d['fecha_limite'] ?? null,               // 'YYYY-mm-dd' o null
            isset($d['intentos_max']) ? (int)$d['intentos_max'] : null,
            (int)($d['obligatorio'] ?? 0),
            $d['estado'] ?? 'activa'                  // activa | pausada | cerrada
        ]);
    }

    /**
     * Crea varias asignaciones para una lista de usuarios (destinatario_tipo='usuario').
     * Devuelve resumen: ['creadas'=>N, 'omitidas'=>M, 'ids'=>[...]]
     * Usa tu mismo patrón llamando a crear() en bucle (sin exec()).
     */
    public function crearParaUsuarios(
        int $ejercicioId,
        int $profesorId,
        array $userIds,
        ?string $fechaLimite = null,
        ?int $intentosMax = null,
        int $obligatorio = 0,
        string $estado = 'activa'
    ): array {
        $res = ['creadas' => 0, 'omitidas' => 0, 'ids' => []];

        foreach ($userIds as $uid) {
            $uid = (int)$uid;
            if ($uid <= 0) {
                $res['omitidas']++;
                continue;
            }

            // Evita duplicados si tienes UNIQUE (destinatario_tipo, destinatario_id, ejercicio_id)
            $ya = $this->buscarUno('usuario', $uid, $ejercicioId);
            if ($ya) {
                $res['omitidas']++;
                continue;
            }

            $id = $this->crear([
                'ejercicio_id'     => $ejercicioId,
                'profesor_id'      => $profesorId,
                'destinatario_tipo' => 'usuario',
                'destinatario_id'  => $uid,
                'fecha_limite'     => $fechaLimite,
                'intentos_max'     => $intentosMax,
                'obligatorio'      => $obligatorio,
                'estado'           => $estado,
            ]);

            if ($id) {
                $res['creadas']++;
                $res['ids'][] = (int)$id;
            } else {
                $res['omitidas']++;
            }
        }

        return $res;
    }

    /** Obtener por id. */
    public function obtener(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE id=? LIMIT 1";
        return $this->select_one($sql, [(int)$id]);
    }

    /** Buscar una asignación concreta por (tipo, destinatario_id, ejercicio_id). */
    public function buscarUno(string $tipo, int $destId, int $ejercicioId): ?array
    {
        $sql = "SELECT * FROM {$this->tabla}
                WHERE destinatario_tipo=? AND destinatario_id=? AND ejercicio_id=?
                LIMIT 1";
        return $this->select_one($sql, [$tipo, (int)$destId, (int)$ejercicioId]);
    }

    /**
     * Listado para un usuario con datos del ejercicio.
     * Filtros opcionales por estado; paginación básica.
     */
    public function listarParaUsuario(int $userId, ?string $estado = null, int $page = 1, int $per = 20): array
    {
        $off = max(0, ($page - 1) * $per);
        $params = ['usuario', (int)$userId];
        $whereEstado = '';

        if ($estado !== null && $estado !== '') {
            $whereEstado = ' AND a.estado = ? ';
            $params[] = $estado;
        }

        $sql = "SELECT a.*,
                       e.titulo, e.nivel, e.es_publico, e.fen_inicial, e.pgn_solucion, e.turno
                FROM {$this->tabla} a
                JOIN ejercicios_publicos e ON e.id = a.ejercicio_id
                WHERE a.destinatario_tipo = ? AND a.destinatario_id = ? {$whereEstado}
                ORDER BY COALESCE(a.fecha_limite, a.id) ASC
                LIMIT {$per} OFFSET {$off}";

        return $this->select($sql, $params);
    }

    /** Actualizar campos sueltos (SET dinámico). */
    public function actualizar(int $id, array $d): bool
    {
        if (!$id) return false;
        $campos = [];
        $params = [];

        $cols = [
            'ejercicio_id',
            'profesor_id',
            'destinatario_tipo',
            'destinatario_id',
            'fecha_limite',
            'intentos_max',
            'obligatorio',
            'estado'
        ];
        foreach ($cols as $c) {
            if (array_key_exists($c, $d)) {
                $campos[] = "{$c} = ?";
                $params[] = $d[$c];
            }
        }
        if (empty($campos)) return true;

        $params[] = (int)$id;
        $sql = "UPDATE {$this->tabla} SET " . implode(', ', $campos) . " WHERE id=?";

        // Como tu base no tiene exec() universal, hacemos PDO directo
        $pdo = $this->conect();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params) !== false;
    }

    /** Cambiar estado rápido. */
    public function actualizarEstado(int $id, string $estado): bool
    {
        $pdo = $this->conect();
        $stmt = $pdo->prepare("UPDATE {$this->tabla} SET estado=? WHERE id=?");
        return $stmt->execute([$estado, (int)$id]) !== false;
    }

    /** Eliminar una asignación. */
    public function eliminar(int $id): bool
    {
        $pdo = $this->conect();
        $stmt = $pdo->prepare("DELETE FROM {$this->tabla} WHERE id=?");
        return $stmt->execute([(int)$id]) !== false;
    }

    /** Eliminar todas las asignaciones de un ejercicio. */
    public function eliminarPorEjercicio(int $ejercicioId): bool
    {
        $pdo = $this->conect();
        $stmt = $pdo->prepare("DELETE FROM {$this->tabla} WHERE ejercicio_id=?");
        return $stmt->execute([(int)$ejercicioId]) !== false;
    }
    public function crearMultiple(
        int $ejercicioId,
        array $userIds,
        int $asignadorId,
        ?string $fechaLimite = null
    ): bool {
        // Limpia y valida lista de usuarios
        $userIds = array_values(array_unique(array_map('intval', $userIds)));
        $userIds = array_filter($userIds, fn($id) => $id > 0);
        if (empty($userIds)) return false;

        // Construye placeholders (8 columnas por fila)
        $values = implode(',', array_fill(0, count($userIds), '(?,?,?,?,?,?,?,?)'));
        $sql = "INSERT IGNORE INTO ejercicios_asignaciones
            (ejercicio_id, profesor_id, destinatario_tipo, destinatario_id, fecha_limite, intentos_max, obligatorio, estado)
            VALUES {$values}";

        // Parámetros para todas las filas
        $params = [];
        foreach ($userIds as $uid) {
            $params[] = (int)$ejercicioId;   // ejercicio_id
            $params[] = (int)$asignadorId;   // profesor_id
            $params[] = 'usuario';           // destinatario_tipo
            $params[] = (int)$uid;           // destinatario_id
            $params[] = $fechaLimite ?: null; // fecha_limite (YYYY-mm-dd) o null
            $params[] = null;                // intentos_max (null = sin límite)
            $params[] = 0;                   // obligatorio (0/1)
            $params[] = 'activa';            // estado (activa|pausada|cerrada)
        }

        $pdo  = $this->conect();
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params) !== false;
    }
}
