<?php
class AdminGaleria extends Controlador
{
    private GaleriaModel $gal;
    public function __construct(){ parent::__construct(); requireAdmin(); $this->gal = new GaleriaModel(); }

    public function index(): void {
        $album = isset($_GET['album']) && $_GET['album']!=='' ? (int)$_GET['album'] : null;
        $tipo  = $_GET['tipo'] ?? '';
        $q     = trim((string)($_GET['q'] ?? '')) ?: null;
        $items = $this->gal->listarMedia($album, $tipo ?: null, $q, false);
        $albums= $this->gal->listarAlbums(false);
        $data  = ['titulo'=>'Galería','csrf'=>csrfToken(),'items'=>$items,'albums'=>$albums,'f'=>compact('album','tipo','q')];
        $this->view('Admin/galeria-index',$data);
    }

    public function crear(): void {
        $albums=$this->gal->listarAlbums(false);
        $data=['titulo'=>'Nuevo medio','csrf'=>csrfToken(),'albums'=>$albums];
        $this->view('Admin/galeria-form',$data);
    }

    public function guardar(): void {
        if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminGaleria/index');
        }
        $tipo = in_array($_POST['tipo'] ?? 'imagen',['imagen','video'],true) ? $_POST['tipo'] : 'imagen';
        $d = [
            'album_id'   => $_POST['album_id']!=='' ? (int)$_POST['album_id'] : null,
            'tipo'       => $tipo,
            'titulo'     => trim((string)($_POST['titulo'] ?? '')),
            'descripcion'=> trim((string)($_POST['descripcion'] ?? '')),
            'youtube_id' => trim((string)($_POST['youtube_id'] ?? '')) ?: null,
            'vimeo_id'   => trim((string)($_POST['vimeo_id'] ?? '')) ?: null,
            'visible'    => isset($_POST['visible']) ? 1 : 0,
            'orden'      => (int)($_POST['orden'] ?? 1),
            'alumno_nombre' => trim((string)($_POST['alumno_nombre'] ?? '')) ?: null,
        ];

        // subida de imagen o video local
        if ($tipo==='imagen' && !empty($_FILES['archivo']['name'])) {
            [$ok,$res]=uploadImage($_FILES['archivo'],'galeria',['jpg','jpeg','png','webp'],8);
            if ($ok) $d['archivo_path']='Assets/img/galeria/'.$res;
        }
        if ($tipo==='video' && !empty($_FILES['video']['name'])) {
            // sube mp4 (usa helper propio si lo tienes)
            $dir = rtrim(BASE_PATH,'/\\').'/Assets/video/galeria/';
            if (!is_dir($dir)) @mkdir($dir,0775,true);
            $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['mp4','webm'],true)) {
                $final = 'vid-'.bin2hex(random_bytes(6)).'.'.$ext;
                if (@move_uploaded_file($_FILES['video']['tmp_name'], $dir.$final)) {
                    $d['video_path'] = 'Assets/video/galeria/'.$final;
                }
            }
        }

        $id = $this->gal->crearMedia($d);
        $_SESSION['flash_ok'] = $id ? 'Elemento creado' : 'No se pudo crear';
        redir(BASE_URL.'AdminGaleria/index');
    }

    public function editar(int $id): void {
        $m = $this->gal->mediaPorId($id);
        if (!$m) { $_SESSION['flash_error']='No encontrado'; redir(BASE_URL.'AdminGaleria/index'); }
        $albums=$this->gal->listarAlbums(false);
        $data=['titulo'=>'Editar medio','csrf'=>csrfToken(),'m'=>$m,'albums'=>$albums];
        $this->view('Admin/galeria-form',$data);
    }

    public function actualizar(int $id): void {
        if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminGaleria/index');
        }
        $m = $this->gal->mediaPorId($id); if (!$m) redir(BASE_URL.'AdminGaleria/index');

        $tipo = in_array($_POST['tipo'] ?? 'imagen',['imagen','video'],true) ? $_POST['tipo'] : 'imagen';
        $d = [
            'album_id'   => $_POST['album_id']!=='' ? (int)$_POST['album_id'] : null,
            'tipo'       => $tipo,
            'titulo'     => trim((string)($_POST['titulo'] ?? '')),
            'descripcion'=> trim((string)($_POST['descripcion'] ?? '')),
            'youtube_id' => trim((string)($_POST['youtube_id'] ?? '')) ?: null,
            'vimeo_id'   => trim((string)($_POST['vimeo_id'] ?? '')) ?: null,
            'archivo_path' => $m['archivo_path'] ?? null,
            'video_path'   => $m['video_path'] ?? null,
            'visible'    => isset($_POST['visible']) ? 1 : 0,
            'orden'      => (int)($_POST['orden'] ?? 1),
            'alumno_nombre' => trim((string)($_POST['alumno_nombre'] ?? '')) ?: null,
        ];

        if ($tipo==='imagen' && !empty($_FILES['archivo']['name'])) {
            [$ok,$res]=uploadImage($_FILES['archivo'],'galeria',['jpg','jpeg','png','webp'],8);
            if ($ok) {
                if (!empty($d['archivo_path'])) deleteFile(basename($d['archivo_path']),'galeria');
                $d['archivo_path']='Assets/img/galeria/'.$res;
            }
        }
        if ($tipo==='video' && !empty($_FILES['video']['name'])) {
            $dir = rtrim(BASE_PATH,'/\\').'/Assets/video/galeria/';
            if (!is_dir($dir)) @mkdir($dir,0775,true);
            $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
            if (in_array($ext,['mp4','webm'],true)) {
                if (!empty($d['video_path']) && is_file(BASE_PATH.$d['video_path'])) @unlink(BASE_PATH.$d['video_path']);
                $final = 'vid-'.bin2hex(random_bytes(6)).'.'.$ext;
                if (@move_uploaded_file($_FILES['video']['tmp_name'], $dir.$final)) {
                    $d['video_path'] = 'Assets/video/galeria/'.$final;
                }
            }
        }

        $this->gal->actualizarMedia($id,$d);
        $_SESSION['flash_ok']='Elemento actualizado';
        redir(BASE_URL.'AdminGaleria/editar/'.$id);
    }

    public function eliminar(int $id): void {
        if (($_SERVER['REQUEST_METHOD'] ?? '')!=='POST' || !verificarCsrf($_POST['csrf'] ?? '')) {
            redir(BASE_URL.'AdminGaleria/index');
        }
        $m=$this->gal->mediaPorId($id);
        if ($m) {
            if (!empty($m['archivo_path'])) deleteFile(basename($m['archivo_path']),'galeria');
            if (!empty($m['video_path']) && is_file(BASE_PATH.$m['video_path'])) @unlink(BASE_PATH.$m['video_path']);
            $this->gal->eliminarMedia($id);
        }
        $_SESSION['flash_ok']='Elemento eliminado';
        redir(BASE_URL.'AdminGaleria/index');
    }
}
