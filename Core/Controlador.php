<?php
class Controlador
{
    protected ?object $model = null;
    protected ?object $view = null;

    public function __construct()
    {
        $this->loadModel();
    }

    public function view($view, $data = [])
    {
        extract($data); // Extrae las variables para usarlas en la vista
        $filePath = "Views/" . $view . ".php"; // Construye la ruta completa
        if (file_exists($filePath)) {
            require_once $filePath; // Incluye la vista
        } else {
            die("La vista $view no existe en $filePath");
        }
    }


    // En el Controlador base
    protected array $models = [];

    public function loadModel(?string $name = null): object
    {
        $name = $name ? rtrim($name, 'Model') . 'Model' : get_class($this) . 'Model';
        $file = BASE_PATH . "Models/{$name}.php";
        if (is_file($file)) require_once $file;
        if (!class_exists($name)) throw new RuntimeException("Modelo {$name} no encontrado");
        return $this->model = new $name();
    }

    public function m(string $name): object
    {
        $name = rtrim($name, 'Model') . 'Model';
        if (isset($this->models[$name])) return $this->models[$name];
        $file = BASE_PATH . "Models/{$name}.php";
        if (is_file($file)) require_once $file;
        if (!class_exists($name)) throw new RuntimeException("Modelo {$name} no encontrado");
        return $this->models[$name] = new $name();
    }
}
