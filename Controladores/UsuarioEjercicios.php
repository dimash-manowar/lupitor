<?php
class UsuarioEjercicios extends Controlador
{
    private EjercicioAsignacionModel $asigModel;
    private EjercicioIntentoModel $intentoModel;
    private EjercicioModel $ejercicioModel;

    public function __construct()
    {
        parent::__construct();
        $this->asigModel     = new EjercicioAsignacionModel();
        $this->intentoModel  = new EjercicioIntentoModel();
        $this->ejercicioModel = new EjercicioModel();
    }

    /** Listado de asignaciones privadas para el usuario logueado */
    public function index()
    {
        requireLogin();
        $uid   = (int)($_SESSION['user']['id'] ?? 0);
        $items = $this->asigModel->listarParaUsuario($uid);

        // Cálculo de estado por ítem
        $ahora = time();
        foreach ($items as &$it) {
            $venc  = !empty($it['fecha_limite']) ? strtotime($it['fecha_limite']) : null;
            $comp  = ((int)($it['completado'] ?? 0) === 1);
            $expir = $venc ? ($ahora > $venc) : false;
            $it['estado_calc'] = $comp ? 'completado' : ($expir ? 'expirado' : 'pendiente');
        }

        $data = [
            'titulo' => 'Mis ejercicios',
            'csrf'   => csrfToken(),
            'items'  => $items,
        ];
        $this->view('Usuario/Ejercicios/index', $data);
    }

    /** Pantalla para resolver una asignación concreta */
    public function resolver(int $asignacionId)
    {
        requireLogin();
        $uid = (int)($_SESSION['user']['id'] ?? 0);

        $asig = $this->asigModel->obtenerPorId($asignacionId);
        if (!$asig || $asig['destinatario_tipo'] !== 'usuario' || (int)$asig['destinatario_id'] !== $uid) {
            $_SESSION['flash_error'] = 'Asignación no válida.';
            redir(BASE_URL . 'UsuarioEjercicios/index');
        }
        if (($asig['estado'] ?? 'activa') !== 'activa') {
            $_SESSION['flash_error'] = 'La asignación no está activa.';
            redir(BASE_URL . 'UsuarioEjercicios/index');
        }

        $intentos = $this->intentoModel->listarPorAsignacionYAlumno($asignacionId, $uid);

        $data = [
            'titulo'   => 'Resolver ejercicio',
            'csrf'     => csrfToken(),
            'asig'     => $asig,
            'intentos' => $intentos,
        ];
        $this->view('Usuario/Ejercicios/resolver', $data);
    }
    

    /** Normaliza y compara SAN; correcto si secuencia exacta */
    private function evaluarIntento(string $pgnEnviado, string $pgnSolucion): array
    {
        $n = function (string $pgn): array {
            // quita headers, comentarios, resultados, variantes, numeración
            $pgn = preg_replace('/\[[^\]]*\]/u', ' ', $pgn);        // headers
            $pgn = preg_replace('/\{[^}]*\}/u', ' ', $pgn);         // comentarios {}
            $pgn = preg_replace('/\([^)]*\)/u', ' ', $pgn);         // variantes ()
            $pgn = preg_replace('/\d+\.(\.\.)?/u', ' ', $pgn);      // 1. 1... etc
            $pgn = preg_replace('/\b(1-0|0-1|1\/2-1\/2|\*)\b/u', ' ', $pgn); // resultados
            $pgn = preg_replace('/\s+/u', ' ', trim($pgn));
            $tokens = $pgn === '' ? [] : explode(' ', $pgn);
            // normaliza signos +, #, x, etc. (dejamos SAN “tal cual” pero en minúsculas)
            return array_values(array_filter(array_map(fn($t) => mb_strtolower(trim($t), 'UTF-8'), $tokens)));
        };

        $a = $n($pgnEnviado);
        $b = $n($pgnSolucion);

        $ok = (!empty($b) && $a === $b);
        $msg = $ok ? '¡Correcto!' : 'La secuencia no coincide con la solución.';
        return ['correcto' => $ok, 'mensaje' => $msg, 'movs_enviados' => $a, 'movs_sol' => $b];
    }
    // Controladores/UsuarioEjercicios.php (añade el método)
    public function entregarPost()
    {
        // JSON puro
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');

        try {
            requireLogin();
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
                return;
            }
            if (!verificarCsrf($_POST['csrf'] ?? '')) {
                echo json_encode(['ok' => false, 'error' => 'CSRF inválido']);
                return;
            }

            $uid   = (int)($_SESSION['user']['id'] ?? 0);
            $asigId = (int)($_POST['asignacion_id'] ?? 0);
            $pgn   = trim((string)($_POST['pgn_intento'] ?? ''));
            $seg   = max(0, (int)($_POST['tiempo_segundos'] ?? 0));

            if (!$uid || !$asigId || $pgn === '') {
                echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
                return;
            }

            
            $notif     = new NotificacionesModel();

            $asig = $this->asigModel->obtenerPorId($asigId);
            if (!$asig || (int)$asig['user_id'] !== $uid) {
                echo json_encode(['ok' => false, 'error' => 'Asignación no encontrada']);
                return;
            }
            if (in_array(strtolower($asig['estado'] ?? ''), ['entregado', 'corregido'], true)) {
                echo json_encode(['ok' => false, 'error' => 'Esta asignación ya fue entregada']);
                return;
            }

            $ej = $this->model->buscarPorId((int)$asig['ejercicio_id']);
            if (!$ej) {
                echo json_encode(['ok' => false, 'error' => 'Ejercicio no disponible']);
                return;
            }

            $pgnSol = (string)($ej['pgn_solucion'] ?? '');
            $correcto = false;
            if ($pgnSol !== '') {
                $a = $this->normalizarSAN($pgn);
                $b = $this->normalizarSAN($pgnSol);
                $correcto = ($a === $b);
            }

            // guarda entrega
            $this->asigModel->marcarEntregado($asigId, [
                'pgn_intento'     => $pgn,
                'tiempo_segundos' => $seg,
                'estado'          => 'entregado',
                'calificacion'    => $correcto ? 100 : null,
                'entregado_en'    => date('Y-m-d H:i:s'),
            ]);

            // notifica al profesor (si existe)
            $profeId = (int)($asig['asignado_por'] ?? 0);
            if ($profeId > 0 && $profeId !== $uid) {
                $notif->crear(
                    $profeId,
                    'ejercicio',
                    'Nueva entrega',
                    'Un alumno ha entregado un ejercicio',
                    BASE_URL . 'Admin/ejerciciosEntregas?asig=' . $asigId,
                    ['asignacion_id' => $asigId, 'alumno_id' => $uid, 'ejercicio_id' => (int)$asig['ejercicio_id']]
                );
            }

            echo json_encode(['ok' => true, 'correcto' => $correcto, 'mensaje' => $correcto ? 'Solución exacta' : 'Enviado para revisión']);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /** Quita numeración, resultados, comentarios y signos de SAN para comparar secuencias. */
    private function normalizarSAN(string $pgn): string
    {
        $s = preg_replace('/\[[^\]]*\]/', ' ', $pgn);    // headers
        $s = preg_replace('/\{[^}]*\}/', ' ', $s);       // comentarios { }
        $s = preg_replace('/\([^\)]*\)/', ' ', $s);      // variantes ( )
        $s = preg_replace('/\$\d+/', ' ', $s);           // NAGs
        $s = preg_replace('/\d+\.(\.\.)?/', ' ', $s);    // numeración 1. 1... etc
        $s = str_replace(['+', '#', '!', '?'], '', $s);  // signos
        $s = str_ireplace(['0-0-0', 'O-O-O'], 'OOO', $s); // unifica enroques
        $s = str_ireplace(['0-0', 'O-O'], 'OO', $s);
        $s = preg_replace('/\b(1-0|0-1|1\/2-1\/2|\*)\b/', ' ', $s); // resultado
        $s = preg_replace('/\s+/', ' ', trim($s));
        return $s;
    }
}
