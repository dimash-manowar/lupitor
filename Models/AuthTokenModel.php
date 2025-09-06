<?php
class AuthTokenModel extends Mysql
{
    protected string $table = 'auth_tokens';
    public function create(int $userId, string $selector, string $hash, string $ua, string $ip, string $expiresAt): ?int
    {
        return $this->insert(
            "INSERT INTO {$this->table}(user_id,selector,validator_hash,user_agent,ip,expires_at) VALUES (?,?,?,?,?,?)",
            [$userId, $selector, $hash, $ua, $ip, $expiresAt]
        );
    }
    public function findBySelector(string $selector): ?array
    {
        return $this->select_one("SELECT * FROM {$this->table} WHERE selector=? LIMIT 1", [$selector]);
    }
    public function deleteByUser(int $userId): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE user_id=?", [$userId]);
    }
    public function deleteById(int $id): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE id=?", [$id]);
    }
    public function purgeExpired(): int
    {
        return $this->delete("DELETE FROM {$this->table} WHERE expires_at < NOW()");
    }
}
