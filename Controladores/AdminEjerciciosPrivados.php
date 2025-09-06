<?php
class AdminEjerciciosPrivados extends Controlador
{
    

    public function ejerciciosAsignarNivel()
    {
        requireAdmin();
        $ejModel  = new EjercicioModel();
        $usrModel = new UsuarioModel();

        $data = [
            'titulo' => 'Asignar ejercicios por nivel',
            'csrf'   => csrfToken(),
            'niveles' => ['Iniciación','Intermedio','Avanzado'],
            'ej_por_nivel' => [
                'Iniciación' => $ejModel->select("SELECT id,titulo FROM ejercicios_publicos WHERE nivel='Iniciación' ORDER BY id DESC"),
                'Intermedio' => $ejModel->select("SELECT id,titulo FROM ejercicios_publicos WHERE nivel='Intermedio' ORDER BY id DESC"),
                'Avanzado'   => $ejModel->select("SELECT id,titulo FROM ejercicios_publicos WHERE nivel='Avanzado'   ORDER BY id DESC"),
            ],
            'counts' => [
                'Iniciación' => count($usrModel->listarIdsPorNivel('Iniciación')),
                'Intermedio' => count($usrModel->listarIdsPorNivel('Intermedio')),
                'Avanzado'   => count($usrModel->listarIdsPorNivel('Avanzado')),
            ],
        ];
        $this->view('Admin/ejercicios-asignar-nivel', $data);
    }

    public function ejerciciosAsignarNivelPost()
    {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        try {
            requireAdmin();
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                http_response_code(405);
                echo json_encode(['ok'=>false,'error'=>'Método no permitido']); return;
            }
            if (!verificarCsrf($_POST['csrf'] ?? '')) {
                echo json_encode(['ok'=>false,'error'=>'CSRF inválido']); return;
            }

            $nivel = $_POST['nivel'] ?? '';
            $idsEj = array_map('intval', $_POST['ejercicio_ids'] ?? []);
            $fecha = trim((string)($_POST['fecha_limite'] ?? '')) ?: null;
            $puntos= (int)($_POST['puntos'] ?? 0);
            $profe = (int)($_SESSION['user']['id'] ?? 0);

            if (!in_array($nivel, ['Iniciación','Intermedio','Avanzado'], true)) {
                echo json_encode(['ok'=>false,'error'=>'Nivel inválido']); return;
            }
            if (empty($idsEj)) {
                echo json_encode(['ok'=>false,'error'=>'Selecciona al menos un ejercicio']); return;
            }

            $usrModel = new UsuarioModel();
            $asigModel= new EjercicioAsignacionModel();
            $notif    = new NotificacionesModel();

            $userIds = $usrModel->listarIdsPorNivel($nivel);
            if (empty($userIds)) {
                echo json_encode(['ok'=>false,'error'=>'No hay alumnos en ese nivel']); return;
            }

            $total = $asigModel->asignarEnBloque($userIds, $idsEj, $profe, $fecha, $puntos);

            // Notificaciones (y email opcional)
            foreach ($userIds as $uid) {
                $link = BASE_URL . 'UsuarioEjercicios/index';
                $notif->crear($uid, 'ejercicio', 'Nuevos ejercicios', 'Tienes nuevas asignaciones', $link, [
                    'nivel'=>$nivel, 'ejercicios'=>count($idsEj)
                ]);

                // Email opcional (descomenta si quieres)
                /*
                $al = (new UsuarioModel())->buscarPorId($uid);
                if ($al && filter_var($al['email'], FILTER_VALIDATE_EMAIL)) {
                    $login = BASE_URL.'auth/login?next='.urlencode('UsuarioEjercicios/index');
                    $sub = 'Nuevos ejercicios asignados';
                    $html = '<p>Hola '.htmlspecialchars($al['nombre'] ?: '').',</p>'.
                            '<p>Tienes nuevos ejercicios de nivel <strong>'.htmlspecialchars($nivel).'</strong>.</p>'.
                            '<p><a href="'.$login.'" style="background:#2ecc71;color:#0b0d12;padding:10px 14px;border-radius:8px;text-decoration:none">Entrar y resolver</a></p>';
                    enviarCorreo($al['email'], $al['nombre'] ?: '', $sub, $html);
                }
                */
            }

            echo json_encode(['ok'=>true,'asignados'=>$total]);
        } catch (Throwable $e) {
            echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
        }
    }
}
