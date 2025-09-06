<?php
class UsuarioModel extends Mysql
{
    protected string $tabla = 'usuarios';


    public function top5Excepto(int $yoId): array
    {
        $sql = "SELECT id, nombre, email FROM {$this->tabla} WHERE id<>? ORDER BY id DESC LIMIT 5";
        return $this->select($sql, [$yoId]);
    }


    public function buscarPorTexto(string $q, int $limit = 10): array
    {
        $like = "%" . $q . "%";
        $sql = "SELECT id, nombre, email FROM {$this->tabla}
WHERE nombre LIKE ? OR email LIKE ?
ORDER BY nombre ASC LIMIT ?";
        $stmt = $this->conect()->prepare($sql);
        $stmt->execute([$like, $like, $limit]);
        return $stmt->fetchAll();
    }


    public function buscarPorId(int $id): ?array
    {
        return $this->select_one("SELECT id, nombre, email, rol, foto_url FROM {$this->tabla} WHERE id=?", [$id]);
    }
    public function buscarPorEmail(string $email): ?array
    {
        $email = mb_strtolower(trim($email), 'UTF-8');
        $sql = "SELECT * FROM {$this->tabla} WHERE LOWER(email)=? LIMIT 1";
        return $this->select_one($sql, [$email]);
    }
    public function crear(array $d): ?int
    {
        $nombre   = trim((string)($d['nombre']   ?? ''));
        $email    = mb_strtolower(trim((string)($d['email'] ?? '')), 'UTF-8');
        $rol      = $d['rol']      ?? 'usuario';
        $foto     = $d['foto_url'] ?? null;

        // Permite 'password' (plano) o 'password_hash' (ya hasheado)
        $hash = null;
        if (!empty($d['password_hash'])) {
            $hash = (string)$d['password_hash'];
        } elseif (!empty($d['password'])) {
            $hash = password_hash((string)$d['password'], PASSWORD_DEFAULT);
        }

        if ($nombre === '' || $email === '' || !$hash) return null;

        $sql = "INSERT INTO {$this->tabla}
            (nombre, email, password_hash, rol, foto_url, creado_en, actualizado_en)
            VALUES (:nombre, :email, :ph, :rol, :foto, NOW(), NOW())";

        try {
            return $this->insert($sql, [
                ':nombre' => $nombre,
                ':email'  => $email,
                ':ph'     => $hash,
                ':rol'    => $rol,
                ':foto'   => $foto
            ]);
        } catch (PDOException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1062) return null; // email duplicado
            throw $e;
        }
    }
    public function obtenerPreferenciasNotificacion(int $id): ?array
    {
        $sql = "SELECT notif_email_mensajes, notif_email_alertas FROM usuarios WHERE id=? LIMIT 1";
        return $this->select_one($sql, [$id]);
    }

    public function actualizarPreferenciasNotificacion(int $id, int $mensajes, int $alertas): int
    {
        $mensajes = $mensajes ? 1 : 0;
        $alertas  = $alertas  ? 1 : 0;
        $sql = "UPDATE usuarios
            SET notif_email_mensajes=?, notif_email_alertas=?
            WHERE id=?";
        return $this->update($sql, [$mensajes, $alertas, $id]);
    }
    public function listarIdsPorNivel(string $nivel): array
    {
        $sql = "SELECT id FROM usuarios WHERE nivel=? AND rol='user'"; // ajusta si usas 'usuario'
        $rows = $this->select($sql, [$nivel]);
        return array_map(fn($r) => (int)$r['id'], $rows);
    }
}
