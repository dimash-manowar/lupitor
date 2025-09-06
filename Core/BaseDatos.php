<?php
class Conexion
{
    protected PDO $conect;
    

    public function __construct()
    {
        {
        // Construimos el DSN (Data Source Name)
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        try {
            $this->conect = new PDO($dsn, DB_USER, DB_PASS);
            // Configuraciones seguras por defecto
            $this->conect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conect->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conect->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
    }

    public function conect()
    {
        return $this->conect;
    }
}
