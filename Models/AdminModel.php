<?php
class AdminModel extends Mysql
{
    protected string $tabla = 'ejercicios_publicos';

    /* ===== CRUD ===== */

    public function actualizar(int $id, array $d): int
    {
        // Campos permitidos y valores por defecto
        $titulo = $d['titulo'] ?? '';
        $nivel = $d['nivel'] ?? 'Iniciación';
        $es_publico = isset($d['es_publico']) ? (int)$d['es_publico'] : 1;
        $fen_inicial = $d['fen_inicial'] ?? '';
        $pgn_solucion = $d['pgn_solucion'] ?? null; // puede ser null
        $turno = ($d['turno'] ?? 'w') === 'b' ? 'b' : 'w';
        $autor_id = $d['autor_id'] ?? null; // opcional


        $sql = "UPDATE {$this->tabla}
SET titulo=?, nivel=?, es_publico=?, fen_inicial=?, pgn_solucion=?, turno=?, autor_id=COALESCE(?, autor_id), updated_at=NOW()
WHERE id=?";
        return $this->update($sql, [
            $titulo,
            $nivel,
            $es_publico,
            $fen_inicial,
            $pgn_solucion,
            $turno,
            $autor_id,
            (int)$id
        ]);
    }


    public function crear(array $d): ?int
    {
        $sql = "INSERT INTO {$this->tabla}
(titulo, nivel, es_publico, fen_inicial, pgn_solucion, turno, autor_id)
VALUES (?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['titulo'],
            $d['nivel'],
            (int)$d['es_publico'],
            $d['fen_inicial'],
            $d['pgn_solucion'] ?? null,
            ($d['turno'] ?? 'w') === 'b' ? 'b' : 'w',
            $d['autor_id'] ?? null
        ]);
    }


    public function obtener(int $id): ?array
    {
        return $this->select_one("SELECT * FROM {$this->tabla} WHERE id=? LIMIT 1", [$id]);
    }


    public function eliminar(int $id): int
    {
        return $this->delete("DELETE FROM {$this->tabla} WHERE id = :id", [':id' => $id]);
    }


    /* ===== Validaciones (básicas) ===== */

    /**
     * Acepta FEN completo o sólo la parte de piezas.
     * Devuelve ['ok'=>bool, 'fen'=>FEN_COMPLETO, 'turno'=>'w|b', 'errores'=>[]].
     */
    public function validarFenNormalizar(string $fenEntrada, ?string $turnoPreferido = null): array
    {
        $fenEntrada = trim($fenEntrada);
        if ($fenEntrada === '') {
            return ['ok' => false, 'errores' => ['FEN vacío']];
        }

        $trozos = preg_split('/\s+/', $fenEntrada);
        $fenPiezas = $trozos[0] ?? '';
        $turno = $trozos[1] ?? ($turnoPreferido ?: 'w');

        // Validación de la parte de piezas
        $err = $this->validarFenPiezas($fenPiezas);
        if (!empty($err)) return ['ok' => false, 'errores' => $err];

        // Normaliza campos faltantes
        $turno = ($turno === 'b') ? 'b' : 'w';
        $fen = "{$fenPiezas} {$turno} - - 0 1";
        return ['ok' => true, 'fen' => $fen, 'turno' => $turno, 'errores' => []];
    }

    /**
     * Valida la PORCIÓN de piezas (8 filas, reyes, peones).
     * Retorna array de errores (vacío si todo ok).
     */
    public function validarFenPiezas(string $fenPiezas): array
    {
        $errores = [];
        $filas = explode('/', $fenPiezas);
        if (count($filas) !== 8) {
            $errores[] = 'El FEN debe tener 8 filas separadas por "/".';
            return $errores;
        }

        $wK = 0;
        $bK = 0;
        $wP = 0;
        $bP = 0;
        foreach ($filas as $i => $fila) {
            $ancho = 0;
            $chars = str_split($fila);
            foreach ($chars as $ch) {
                if (ctype_digit($ch)) {
                    $ancho += (int)$ch;
                } elseif (preg_match('/[prnbqkPRNBQK]/', $ch)) {
                    $ancho += 1;
                    if ($ch === 'K') $wK++;
                    if ($ch === 'k') $bK++;
                    if ($ch === 'P') {
                        $wP++;
                        $rank = 8 - $i;
                        if ($rank === 1 || $rank === 8) $errores[] = "Peón blanco en fila {$rank} (no permitido).";
                    }
                    if ($ch === 'p') {
                        $bP++;
                        $rank = 8 - $i;
                        if ($rank === 1 || $rank === 8) $errores[] = "Peón negro en fila {$rank} (no permitido).";
                    }
                } else {
                    $errores[] = "Carácter inválido en FEN: '{$ch}'.";
                }
            }
            if ($ancho !== 8) $errores[] = "La fila " . (8 - $i) . " no suma 8 casillas (suma {$ancho}).";
        }

        if ($wK !== 1) $errores[] = "Debe haber exactamente 1 rey blanco (hay {$wK}).";
        if ($bK !== 1) $errores[] = "Debe haber exactamente 1 rey negro (hay {$bK}).";
        if ($wP > 8)   $errores[] = "Demasiados peones blancos ({$wP} > 8).";
        if ($bP > 8)   $errores[] = "Demasiados peones negros ({$bP} > 8).";

        return $errores;
    }



    private function escaparLike(string $q): string
    {
        return strtr($q, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']);
    }

    /** Devuelve la cláusula ORDER BY segura a partir de un alias 'orden' */
    private function orderByDesdeAlias(?string $orden): string
    {
        switch ($orden) {
            case 'antiguos':
                return 'created_at ASC, id ASC';
            case 'nivel_asc':
                return "FIELD(nivel,'Iniciación','Intermedio','Avanzado') ASC, id DESC";
            case 'nivel_desc':
                return "FIELD(nivel,'Iniciación','Intermedio','Avanzado') DESC, id DESC";
            case 'titulo_asc':
                return 'titulo ASC, id DESC';
            case 'titulo_desc':
                return 'titulo DESC, id DESC';
            case 'recientes':
            default:
                return 'created_at DESC, id DESC';
        }
    }

    /** Listado con filtros + orden + paginación */
    public function listar(array $f = [], int $page = 1, int $per = 20): array
    {
        // saneo básico de paginación
        $page = max(1, (int)$page);
        $per  = max(1, min(100, (int)$per));
        $off  = max(0, ($page - 1) * $per);

        // filtros
        $where = [];
        $p = [];

        if (!empty($f['nivel'])) {
            // valida nivel contra los permitidos (opcional)
            $niv = (string)$f['nivel'];
            if (in_array($niv, ['Iniciación', 'Intermedio', 'Avanzado'], true)) {
                $where[] = 'nivel = :nivel';
                $p[':nivel'] = $niv;
            }
        }

        if (array_key_exists('es_publico', $f) && $f['es_publico'] !== '' && $f['es_publico'] !== null) {
            $where[] = 'es_publico = :pub';
            $p[':pub'] = (int)$f['es_publico']; // 0 | 1
        }

        if (!empty($f['q'])) {
            $q = '%' . $this->escaparLike(trim((string)$f['q'])) . '%';
            $where[] = "titulo LIKE :q ESCAPE '\\\\'";
            $p[':q'] = $q;
        }

        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // orden desde alias (con fallback)
        $order = $this->orderByDesdeAlias($f['orden'] ?? null) ?: 'id DESC';

        // consulta principal (LIMIT/OFFSET ya saneados como enteros)
        $items = $this->select(
            "SELECT * FROM {$this->tabla} {$w} ORDER BY {$order} LIMIT {$per} OFFSET {$off}",
            $p
        );

        // total
        $row   = $this->select_one("SELECT COUNT(*) AS c FROM {$this->tabla} {$w}", $p) ?? ['c' => 0];
        $total = (int)$row['c'];

        return [
            'items'       => $items,
            'page'        => $page,
            'per'         => $per,
            'total'       => $total,
            'total_pages' => max(1, (int)ceil($total / $per)),
        ];
    }


    public function listarPublico(?string $nivel = null, int $page = 1, int $per = 20, ?string $q = null, ?string $orden = 'recientes'): array
    {
        return $this->listar(['nivel' => $nivel, 'es_publico' => 1, 'q' => $q, 'orden' => $orden], $page, $per);
    }
}
