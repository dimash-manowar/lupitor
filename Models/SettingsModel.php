<?php
class SettingsModel extends Mysql
{
  protected string $table = 'ajustes';

  public function get(string $key, ?string $default=null): ?string {
    $row = $this->select_one("SELECT valor FROM {$this->table} WHERE clave=?", [$key]);
    return $row['valor'] ?? $default;
  }

  public function getInt(string $key, int $default=0): int {
    return (int)($this->get($key, (string)$default));
  }

  public function set(string $key, string $value): int {
    return $this->update(
      "INSERT INTO {$this->table} (clave,valor) VALUES (?,?)
       ON DUPLICATE KEY UPDATE valor=VALUES(valor)",
      [$key, $value]
    );
  }
}
