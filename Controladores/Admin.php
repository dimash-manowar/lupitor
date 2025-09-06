<?php
class Admin extends Controlador
{    

    public function __construct()
    {
        parent::__construct();
        requireAdmin();
        
    }
    public function index()
    {
        requireAdmin(); 


        // Métricas (si las tablas no existen, devuelven 0)
        $usuarios = $this->model->countIfExists('usuarios');
        $noticias = $this->model->countIfExists('noticias');
        $torneos = $this->model->countIfExists('torneos');
        $inscripciones = $this->model->countIfExists('inscripciones');


        // Últimas noticias
        $ultimas = $this->model->ultimasNoticias(5);


        $data = [
            'titulo' => 'Dashboard',
            'csrf' => csrfToken(),
            'metricas' => compact('usuarios', 'noticias', 'torneos', 'inscripciones'),
            'ultimas' => $ultimas,
        ];
        $this->view('Admin/index', $data);
    }


    // helper para contadores topbar (llámalo en cada acción admin)
    private function addTopbarCounts(array &$data): void
    {
        $uid = $_SESSION['user']['id'] ?? null;
        $data['unread_notif'] = $this->model->contarNoLeidas($uid);
        $data['unread_msgs']  = $this->model->contarPorEstado('nuevo');
    }

    /* ===== NOTIFICACIONES ===== */
    public function notificaciones($page = 1): void
    {

        $soloNoLeidas = isset($_GET['solo']) ? (int)$_GET['solo'] : null;
        $uid = $_SESSION['user']['id'] ?? null;
        $list = $this->model->listar((int)$page, 12, $uid, $soloNoLeidas);
        $data = array_merge($list, [
            'titulo' => 'Notificaciones',
            'csrf'   => csrfToken(),
            'solo'   => $soloNoLeidas,
        ]);
        $this->addTopbarCounts($data);
        $this->view('Admin/notificaciones-lista', $data);
    }

    public function notifLeer($id): void
    {

        $id = (int)$id;
        $n = $this->model->obtener($id);
        if ($n) {
            $this->model->marcarLeida($id);
            if (!empty($n['link'])) {
                header('Location: ' . $n['link']);
                exit;
            }
        }
        header('Location: ' . BASE_URL . 'admin/notificaciones');
        exit;
    }

    public function notifEliminar($id): void
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/notificaciones');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');
        $this->model->eliminar((int)$id);
        $_SESSION['flash_ok'] = 'Notificación eliminada';
        header('Location: ' . BASE_URL . 'admin/notificaciones');
        exit;
    }

    /* ===== MENSAJES ===== */
    public function mensajes($page = 1): void
    {

        $estado = $_GET['estado'] ?? null;
        $buscar = $_GET['q'] ?? null;
        $list = $this->model->listar((int)$page, 12, $estado ?: null, $buscar ?: null);
        $data = array_merge($list, [
            'titulo' => 'Mensajes',
            'csrf'   => csrfToken(),
            'estado' => $estado,
            'buscar' => $buscar,
        ]);
        $this->addTopbarCounts($data);
        $this->view('Admin/mensajes-lista', $data);
    }

    public function mensajeVer($id): void
    {

        $msg = $this->model->obtener((int)$id);
        if (!$msg) {
            $_SESSION['flash_error'] = 'Mensaje no encontrado';
            header('Location: ' . BASE_URL . 'admin/mensajes');
            exit;
        }
        if ($msg['estado'] === 'nuevo') {
            $this->model->marcarLeido((int)$id);
        }
        $data = ['titulo' => 'Mensaje', 'csrf' => csrfToken(), 'm' => $msg];
        $this->addTopbarCounts($data);
        $this->view('Admin/mensajes-ver', $data);
    }

    // POST /admin/mensajeResponder/{id}
    public function mensajeResponderPost($id): void
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/mensajes');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');
        $id = (int)$id;
        $msg = $this->model->obtener($id);
        if (!$msg) {
            $_SESSION['flash_error'] = 'Mensaje no encontrado';
            header('Location: ' . BASE_URL . 'admin/mensajes');
            exit;
        }

        $subject = trim($_POST['subject'] ?? '');
        $body    = trim($_POST['body'] ?? '');
        if (!$subject || !$body) {
            $_SESSION['flash_error'] = 'Asunto y respuesta son obligatorios';
            header('Location: ' . BASE_URL . 'admin/mensajeVer/' . $id);
            exit;
        }

        // Enviar correo al remitente
        $embedded = [];
        $cid = null;
        $logoPath = BASE_PATH . "Assets/img/logo-ajedrez.png";
        if (is_file($logoPath)) {
            $cid = "logo_" . bin2hex(random_bytes(4));
            $embedded[] = ['path' => $logoPath, 'cid' => $cid, 'name' => 'logo.png'];
        }
        $html = nl2br(htmlspecialchars($body));
        $html = "<div style=\"font-family:Arial,sans-serif;color:#111\"><p>Hola " . htmlspecialchars($msg['nombre']) . ",</p><p>{$html}</p><hr><small>Club de Ajedrez de Berriozar</small></div>";

        enviarCorreo($msg['email'], $msg['nombre'], $subject, $html, strip_tags($body), $embedded);

        // Guardar respuesta
        $this->model->responder($id, $subject, $body);
        $_SESSION['flash_ok'] = 'Respuesta enviada';
        header('Location: ' . BASE_URL . 'admin/mensajes');
        exit;
    }

    public function mensajeEliminar($id): void
    {

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . 'admin/mensajes');
            exit;
        }
        verificarCsrf($_POST['csrf'] ?? '');
        $this->model->eliminar((int)$id);
        $_SESSION['flash_ok'] = 'Mensaje eliminado';
        header('Location: ' . BASE_URL . 'admin/mensajes');
        exit;
    }


    public function ver(int $id): void
    {
        $ej = $this->ejercicioModel->obtener($id);
        if (!$ej) {
            $this->json(['error' => 'No encontrado'], 404);
        }
        $this->json($ej);
    }




    public function borrar(int $id): void
    {
        $this->requerirPostCsrf();
        $ok = $this->ejercicioModel->eliminar($id);
        $this->json(['ok' => $ok]);
    }

    /* ------- helpers internos ------- */

    private function sanitizar(): array
    {
        $f = fn($k) => trim((string)($_POST[$k] ?? ''));
        return [
            'titulo'       => mb_substr($f('titulo'), 0, 255),
            'descripcion'  => $f('descripcion'),
            'fen'          => mb_substr($f('fen'), 0, 100),
            'solucion_pgn' => $f('solucion_pgn'),
            'dificultad'   => $f('dificultad') ?: 'Medio',
            'categoria'    => mb_substr($f('categoria'), 0, 100),
        ];
    }

    private function requerirPostCsrf(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }
        verificarCsrf($_POST['csrf'] ?? '');
    }

    private function json($data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public function ejercicios(int $pagina = 1): void
    {


        $nivel = $_GET['nivel'] ?? '';
        $nivel = in_array($nivel, ['Iniciación', 'Intermedio', 'Avanzado'], true) ? $nivel : '';

        $pubRaw = $_GET['pub'] ?? '';
        $pub = ($pubRaw !== '' ? (int)$pubRaw : null); // 0|1|null

        $filtros = [
            'nivel'      => $nivel,                  // '' si no filtra
            'es_publico' => $pub,                    // 0|1|null
            'q'          => $_GET['q']    ?? '',     // búsqueda opcional
            'orden'      => $_GET['orden'] ?? null,   // alias de orden opcional
        ];

        $r = $this->ejercicioModel->listar($filtros, (int)$pagina, 20);

        $data = [
            'titulo' => 'Ejercicios',
            'csrf'   => csrfToken(),
            'nivel'  => $nivel,
            'pub'    => $pubRaw,
            'items'  => $r['items'],
            'meta'   => [
                'page'        => $r['page'],
                'per'         => $r['per'],
                'total'       => $r['total'],
                'total_pages' => $r['total_pages'],
                'q'           => $filtros['q'],
                'orden'       => $filtros['orden'],
            ],
        ];
        $this->view('Admin/ejercicios-index', $data);
    }


    public function ejerciciosSolucionGuardar(): void
    {
        // Acepta JSON {id, fen_inicial, pgn_solucion}
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Método no permitido'], 405);
        }


        // El payload puede venir por JSON (fetch) o por POST normal
        $body = file_get_contents('php://input');
        $data = json_decode($body, true);
        if (!is_array($data)) $data = $_POST;
        $id = (int)($data['id'] ?? 0);
        $fen = trim((string)($data['fen_inicial'] ?? ''));
        $pgn = trim((string)($data['pgn_solucion'] ?? '')) ?: null;


        if ($id <= 0 || $fen === '') {
            $this->json(['ok' => false, 'error' => 'Datos incompletos'], 400);
        }


        $val = $this->ejercicioModel->validarFenNormalizar($fen, null);
        if (!$val['ok']) {
            $this->json(['ok' => false, 'error' => implode('; ', $val['errores'])], 422);
        }


        $ok = $this->ejercicioModel->actualizar($id, [
            'fen_inicial' => $val['fen'],
            'turno' => $val['turno'],
            'pgn_solucion' => $pgn,
            'autor_id' => $_SESSION['user']['id'] ?? null,
        ]);
        $this->json(['ok' => $ok >= 0]);
    }

    public function ejerciciosCrearPost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['flash_error'] = 'Solicitud inválida.';
            header('Location: ' . BASE_URL . 'admin/ejercicios');
            exit;
        }
        [$ok, $datos, $errores] = $this->validarEntrada($_POST);
        if (!$ok) {
            $_SESSION['flash_error'] = implode(' | ', $errores);
            // Re-render con datos y errores si prefieres evitar perder el formulario:
            $this->view('Admin/ejercicios-form', ['titulo' => 'Nuevo ejercicio', 'csrf' => csrfToken(), 'ej' => $datos, 'errores' => $errores]);
            return;
        }
        $id = $this->ejercicioModel->crear($datos);
        $_SESSION['flash_uccess'] = 'Ejercicio creado'; // (typo corregido justo abajo)
        $_SESSION['flash_success'] = 'Ejercicio creado';
        header('Location: ' . BASE_URL . 'admin/ejercicios');
        exit;
    }

    public function ejerciciosEditarPost(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            $_SESSION['flash_error'] = 'Solicitud inválida.';
            header('Location: ' . BASE_URL . 'admin/ejercicios');
            exit;
        }
        $existente = $this->ejercicioModel->obtener((int)$id);
        if (!$existente) {
            $_SESSION['flash_error'] = 'Ejercicio no encontrado';
            header('Location: ' . BASE_URL . 'admin/ejercicios');
            exit;
        }

        [$ok, $datos, $errores] = $this->validarEntrada($_POST);
        if (!$ok) {
            $_SESSION['flash_error'] = implode(' | ', $errores);
            $datos['id'] = $id;
            $this->view('Admin/ejercicios-form', ['titulo' => 'Editar ejercicio', 'csrf' => csrfToken(), 'ej' => $datos, 'errores' => $errores]);
            return;
        }
        $this->ejercicioModel->actualizar((int)$id, $datos);
        $_SESSION['flash_success'] = 'Ejercicio actualizado';
        header('Location: ' . BASE_URL . 'admin/ejercicios');
        exit;
    }

    /** ---------- Validación y saneado ---------- */
    private function validarEntrada(array $src): array
    {
        $f = fn($k) => trim((string)($src[$k] ?? ''));
        $datos = [
            'titulo'       => mb_substr($f('titulo'), 0, 255),
            'descripcion'  => $f('descripcion'),
            'fen'          => mb_substr($f('fen'), 0, 100),
            'solucion_pgn' => $f('solucion_pgn'),
            'dificultad'   => in_array($f('dificultad'), ['Fácil', 'Medio', 'Difícil']) ? $f('dificultad') : 'Medio',
            'categoria'    => mb_substr($f('categoria'), 0, 100),
            'nivel'        => in_array($f('nivel'), ['Iniciación', 'Intermedio', 'Avanzado']) ? $f('nivel') : 'Iniciación',
            'es_publico'   => isset($src['es_publico']) ? 1 : 0,
        ];

        $errores = [];
        if ($datos['titulo'] === '') $errores[] = 'El título es obligatorio.';
        if ($datos['fen'] === '')    $errores[] = 'La posición (FEN) es obligatoria.';
        if ($datos['fen'] && !$this->validarFenBasico($datos['fen'])) $errores[] = 'FEN inválido.';

        return [empty($errores), $datos, $errores];
    }

    private function validarFenBasico(string $fen): bool
    {
        // Validación ligera: 6 campos, filas válidas y turno w/b
        $partes = explode(' ', trim($fen));
        if (count($partes) < 4) return false;
        [$tablero, $turno] = [$partes[0], $partes[1]];
        if (!in_array($turno, ['w', 'b'])) return false;
        $filas = explode('/', $tablero);
        if (count($filas) !== 8) return false;
        foreach ($filas as $fila) {
            $cuentas = 0;
            for ($i = 0; $i < strlen($fila); $i++) {
                $c = $fila[$i];
                if (ctype_digit($c)) $cuentas += (int)$c;
                elseif (preg_match('/[prnbqkPRNBQK]/', $c)) $cuentas += 1;
                else return false;
            }
            if ($cuentas !== 8) return false;
        }
        return true;
    }

    /* ===== CREAR ===== */
    public function ejerciciosCrear(): void
    {


        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // 1) recoger datos
            $titulo      = trim($_POST['titulo'] ?? '');
            $nivel       = $_POST['nivel'] ?? 'Iniciación';
            $es_publico  = isset($_POST['es_publico']) ? 1 : 0;
            $fen_input   = trim($_POST['fen_inicial'] ?? '');
            $pgn_sol     = trim($_POST['pgn_solucion'] ?? '');
            $turno_sel   = ($_POST['turno'] ?? ($_POST['sideToMove'] ?? 'w')) === 'b' ? 'b' : 'w';

            // 2) validar
            $errores = [];
            if ($titulo === '') $errores[] = 'El título es obligatorio.';
            if (!in_array($nivel, ['Iniciación', 'Intermedio', 'Avanzado'], true)) $errores[] = 'Nivel no válido.';

            $fenPieces = explode(' ', $fen_input)[0] ?? $fen_input;
            $val = $this->ejercicioModel->validarFenNormalizar($fen_input, $turno_sel);
            if (!$val['ok']) $errores = array_merge($errores, $val['errores']);

            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode("\n", $errores);
                // repoblar
                $data = [
                    'titulo' => 'Nuevo ejercicio',
                    'csrf' => csrfToken(),
                    'e' => [
                        'titulo' => $titulo,
                        'nivel' => $nivel,
                        'es_publico' => $es_publico,
                        'fen_inicial' => $fen_input,
                        'pgn_solucion' => $pgn_sol
                    ]
                ];
                $this->addTopbarCounts($data);
                $this->view('Admin/ejercicios-form', $data);
                return;
            }

            // 3) crear
            $nuevoId = $this->ejercicioModel->crear([
                'titulo'       => $titulo,
                'nivel'        => $nivel,
                'es_publico'   => $es_publico,
                'fen_inicial'  => $val['fen'],      // FEN normalizado completo
                'pgn_solucion' => $pgn_sol ?: null,
                'turno'        => $val['turno'],
                'autor_id'     => $_SESSION['user']['id'] ?? null,
            ]);

            if ($nuevoId) {
                $_SESSION['flash_ok'] = 'Ejercicio creado correctamente.';
                header('Location: ' . BASE_URL . 'admin/ejercicios');
                exit;
            } else {
                $_SESSION['flash_error'] = 'No se pudo crear el ejercicio.';
                header('Location: ' . BASE_URL . 'admin/ejerciciosCrear');
                exit;
            }
        }

        // GET → dibuja formulario vacío
        $data = ['titulo' => 'Nuevo ejercicio', 'csrf' => csrfToken()];
        $this->addTopbarCounts($data);
        $this->view('Admin/ejercicios-form', $data);
    }

    /* ===== EDITAR ===== */
    public function ejerciciosEditar(int $id): void
    {


        $ej = $this->ejercicioModel->obtener($id);
        if (!$ej) {
            $_SESSION['flash_error'] = 'Ejercicio no encontrado.';
            header('Location: ' . BASE_URL . 'admin/ejercicios');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo      = trim($_POST['titulo'] ?? '');
            $nivel       = $_POST['nivel'] ?? 'Iniciación';
            $es_publico  = isset($_POST['es_publico']) ? 1 : 0;
            $fen_input   = trim($_POST['fen_inicial'] ?? '');
            $pgn_sol     = trim($_POST['pgn_solucion'] ?? '');
            $turno_sel   = ($_POST['turno'] ?? ($_POST['sideToMove'] ?? 'w')) === 'b' ? 'b' : 'w';

            $errores = [];
            if ($titulo === '') $errores[] = 'El título es obligatorio.';
            if (!in_array($nivel, ['Iniciación', 'Intermedio', 'Avanzado'], true)) $errores[] = 'Nivel no válido.';
            $val = $this->ejercicioModel->validarFenNormalizar($fen_input, $turno_sel);
            if (!$val['ok']) $errores = array_merge($errores, $val['errores']);

            if (!empty($errores)) {
                $_SESSION['flash_error'] = implode("\n", $errores);
                $data = ['titulo' => 'Editar ejercicio', 'csrf' => csrfToken(), 'e' => array_merge($ej, [
                    'titulo' => $titulo,
                    'nivel' => $nivel,
                    'es_publico' => $es_publico,
                    'fen_inicial' => $fen_input,
                    'pgn_solucion' => $pgn_sol
                ])];
                $this->addTopbarCounts($data);
                $this->view('Admin/ejercicios-form', $data);
                return;
            }

            $ok = $this->ejercicioModel->actualizar($id, [
                'titulo' => $titulo,
                'nivel' => $nivel,
                'es_publico' => $es_publico,
                'fen_inicial' => $val['fen'],
                'pgn_solucion' => $pgn_sol ?: null,
                'turno' => $val['turno']
            ]);

            if ($ok >= 0) {
                $_SESSION['flash_ok'] = 'Ejercicio actualizado.';
                header('Location: ' . BASE_URL . 'admin/ejercicios');
                exit;
            }
            $_SESSION['flash_error'] = 'No se pudo actualizar.';
            header('Location: ' . BASE_URL . 'admin/ejerciciosEditar/' . $id);
            exit;
        }

        // GET → dibuja formulario con datos
        $data = ['titulo' => 'Editar ejercicio', 'csrf' => csrfToken(), 'e' => $ej];
        $this->addTopbarCounts($data);
        $this->view('Admin/ejercicios-form', $data);
    }

    /* (Opcional) ELIMINAR rápido */
    public function ejerciciosEliminar(int $id): void
    {

        $this->ejercicioModel->eliminar($id);
        $_SESSION['flash_ok'] = 'Ejercicio eliminado.';
        header('Location: ' . BASE_URL . 'admin/ejercicios');
        exit;
    }
}
