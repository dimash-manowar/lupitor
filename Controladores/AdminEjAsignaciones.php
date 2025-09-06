<?php
class AdminEjAsignaciones extends Controlador
{
    private EjercicioModel $ejercicioModel;
    private EjercicioAsignacionModel $asigModel;
    private UsuarioModel $usuariosModel; // asume que existe; si no, impl. mínima abajo


    public function __construct()
    {
        parent::__construct();
        requireAdmin();
        $this->ejercicioModel = new EjercicioModel();
        $this->asigModel = new EjercicioAsignacionModel();
        $this->usuariosModel = new UsuarioModel();
    }


    // GET /AdminEjAsignaciones/asignar/{ejercicioId}
    public function asignar(int $ejercicioId): void
    {
        $ej = $this->ejercicioModel->obtener($ejercicioId);
        if (!$ej) {
            $_SESSION['flash_error'] = 'Ejercicio no encontrado';
            header('Location: ' . BASE_URL . 'admin/ejercicios');
            exit;
        }
        $usuarios = $this->ejercicioModel->listar();
        $data = [
            'titulo' => 'Asignar ejercicio',
            'csrf' => csrfToken(),
            'ej' => $ej,
            'usuarios' => $usuarios,
        ];
        $this->view('Admin/ejercicios-asignar', $data);
    }


    // POST /AdminEjAsignaciones/asignarGuardar
    public function asignarGuardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['flash_error'] = 'Solicitud inválida';
            header('Location: ' . BASE_URL . 'admin/ejercicios');
            exit;
        }
        $ejercicioId = (int)($_POST['ejercicio_id'] ?? 0);
        $uids = array_map('intval', $_POST['usuarios'] ?? []);
        $fechaLimite = trim((string)($_POST['fecha_limite'] ?? '')) ?: null;
        if ($ejercicioId <= 0 || empty($uids)) {
            $_SESSION['flash_error'] = 'Selecciona al menos un usuario';
            header('Location: ' . BASE_URL . 'AdminEjAsignaciones/asignar/' . $ejercicioId);
            exit;
        }
        $profId = $_SESSION['user']['id'] ?? 0;
        $ok = (new EjercicioAsignacionModel())->crearMultiple($ejercicioId, $uids, $profId, $fechaLimite);
        if ($ok) {
            $_SESSION['flash_ok'] = 'Asignaciones creadas';
        } else {
            $_SESSION['flash_error'] = 'No se pudieron crear las asignaciones';
        }
        header('Location: ' . BASE_URL . 'admin/ejercicios');
        exit;
    }
}
