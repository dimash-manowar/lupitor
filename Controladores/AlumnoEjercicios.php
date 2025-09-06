<?php
class AlumnoEjercicios extends Controlador
{
    private EjercicioAsignacionModel $asigModel;
    private EjercicioIntentoModel $intentoModel;


    public function __construct()
    {
        parent::__construct();
        requireLogin();
        $this->asigModel = new EjercicioAsignacionModel();
        $this->intentoModel = new EjercicioIntentoModel();
    }


    // GET /AlumnoEjercicios/mis
    public function mis(int $page = 1): void
    {
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $estado = $_GET['estado'] ?? null; // activa|pausada|cerrada|NULL
        $items = $this->asigModel->listarParaUsuario($uid, $estado, (int)$page, 20);
        $data = [
            'titulo' => 'Mis ejercicios',
            'items' => $items,
        ];
        $this->view('Alumno/ejercicios-lista', $data);
    }


    // GET /AlumnoEjercicios/resolver/{asigId}
    public function resolver(int $asigId): void
    {
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $asig = $this->asigModel->obtener($asigId);
        if (!$asig || $asig['destinatario_tipo'] !== 'usuario' || (int)$asig['destinatario_id'] !== $uid) {
            $_SESSION['flash_error'] = 'Asignación no encontrada';
            header('Location: ' . BASE_URL . 'AlumnoEjercicios/mis');
            exit;
        }
        // Trae datos del ejercicio
        $ej = (new EjercicioModel())->obtener((int)$asig['ejercicio_id']);
        $intentos = $this->intentoModel->listarPorAsignacionYAlumno($asigId, $uid);
        $mejor = $this->intentoModel->mejorPorAsignacionYAlumno($asigId, $uid);
        $data = ['titulo' => 'Resolver ejercicio', 'asig' => $asig, 'ej' => $ej, 'intentos' => $intentos, 'mejor' => $mejor, 'csrf' => csrfToken()];
        $this->view('Alumno/ejercicio-resolver', $data);
    }
    private function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    // POST /AlumnoEjercicios/guardarIntento/{asigId}
    public function guardarIntento(int $asigId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            $this->json(['error' => 'Solicitud inválida'], 400);
        }
        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $pgn = trim((string)($_POST['pgn'] ?? '')) ?: null;
        $correcto = (int)($_POST['correcto'] ?? 0);
        $movs = isset($_POST['movimientos']) ? (int)$_POST['movimientos'] : null;
        $tsec = isset($_POST['tiempo_seg']) ? (int)$_POST['tiempo_seg'] : null;
        $punt = isset($_POST['puntuacion']) ? (int)$_POST['puntuacion'] : null;
        $coment = trim((string)($_POST['comentario'] ?? '')) ?: null;


        $id = $this->intentoModel->crear([
            'asignacion_id' => $asigId,
            'alumno_id' => $uid,
            'pgn_enviado' => $pgn,
            'correcto' => $correcto,
            'movimientos' => $movs,
            'tiempo_seg' => $tsec,
            'puntuacion' => $punt,
            'comentario' => $coment,
        ]);
        $this->json(['ok' => (bool)$id, 'id' => $id]);
    }
}
