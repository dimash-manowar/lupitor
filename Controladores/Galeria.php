<?php
class Galeria extends Controlador
{
    private GaleriaModel $gal;
    public function __construct()
    {
        parent::__construct();
        $this->gal = new GaleriaModel();
    }

    public function index(): void
    {
        $albums = $this->gal->listarAlbums(true);
        $data = ['titulo' => 'Galería', 'albums' => $albums];
        $this->view('Galeria/index', $data);
    }

    public function album(string $slug): void
    {
        $a = $this->gal->albumPorSlug($slug);
        if (!$a || !(int)$a['visible']) {
            redir(BASE_URL . 'Galeria/index');
        }

        // Por defecto: un único listado (compatibilidad con otros álbumes)
        $items = $this->gal->listarMedia((int)$a['id'], null, null, true);

        // Si es el álbum de alumnos: separamos en dos arrays (fotos / vídeos)
        $fotos  = $videos = [];
        if (in_array($a['slug'], ['alumnos', 'alumnos-del-club'], true)) {
            $fotos  = $this->gal->listarMedia((int)$a['id'], 'imagen', null, true);
            $videos = $this->gal->listarMedia((int)$a['id'], 'video',  null, true);
        }

        $data = ['titulo' => $a['titulo'], 'a' => $a, 'items' => $items, 'fotos' => $fotos, 'videos' => $videos];
        $this->view('Galeria/album', $data);
    }
}
