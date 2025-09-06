<?php
// Core/Mysql.php
class Mysql extends Conexion
{
    public function __construct()
    {
        parent::__construct(); // crea $this->conect (PDO) en Conexion
    }

    /**
     * Obtiene una sola fila o null si no hay resultados.
     */
    public function select_one(string $query, array $arrValues = []): ?array
    {
        $stmt = $this->conect->prepare($query);
        $stmt->execute($arrValues);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row; // siempre array o null
    }

    /**
     * Obtiene todas las filas (array de arrays). Si no hay resultados, devuelve [].
     */
    public function select(string $query, array $arrValues = []): array
    {
        $stmt = $this->conect->prepare($query);
        $stmt->execute($arrValues);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * INSERT con retorno del id insertado (o null si falla).
     */
    public function insert(string $query, array $arrValues = []): ?int
    {
        $stmt = $this->conect->prepare($query);
        $ok = $stmt->execute($arrValues);
        if (!$ok) return null;

        $id = $this->conect->lastInsertId();
        return $id ? (int)$id : null;
    }

    /**
     * UPDATE. Devuelve número de filas afectadas.
     */
    public function update(string $query, array $arrValues = []): int
    {
        $stmt = $this->conect->prepare($query);
        $stmt->execute($arrValues);
        return $stmt->rowCount();
    }

    /**
     * DELETE. Devuelve número de filas afectadas.
     */
    public function delete(string $query, array $arrValues = []): int
    {
        $stmt = $this->conect->prepare($query);
        $stmt->execute($arrValues);
        return $stmt->rowCount();
    }

    /**
     * Ejecuta una consulta arbitraria y devuelve el PDOStatement (por si necesitas fetch manual).
     */
    public function query(string $query, array $arrValues = []): PDOStatement
    {
        $stmt = $this->conect->prepare($query);
        $stmt->execute($arrValues);
        return $stmt;
    }

    /**
     * Transacciones sencillas.
     */
    public function begin(): void
    {
        if (!$this->conect->inTransaction()) {
            $this->conect->beginTransaction();
        }
    }

    public function commit(): void
    {
        if ($this->conect->inTransaction()) {
            $this->conect->commit();
        }
    }

    public function rollBack(): void
    {
        if ($this->conect->inTransaction()) {
            $this->conect->rollBack();
        }
    }
}
