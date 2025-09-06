<?php
// Models/PasswordResetModel.php
class PasswordResetModel extends Mysql
{
    protected string $table = 'password_resets';
    public function crear(int $userId, string $selector, string $hash, string $expiresAt): ?int
    {
        return $this->insert(
            "INSERT INTO {$this->table}(user_id,selector,validator_hash,expires_at) VALUES (?,?,?,?)",
            [$userId, $selector, $hash, $expiresAt]
        );
    }
    public function deleteByUser(int $userId): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE user_id=?", [$userId]);
    }
    public function purgeExpired(): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE expires_at < NOW()");
    }
    public function findBySelector(string $selector): ?array
    {
        return $this->select_one("SELECT * FROM {$this->table} WHERE selector=? LIMIT 1", [$selector]);
    }
    public function deleteById(int $id): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }
}
