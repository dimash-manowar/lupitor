<?php
class RolesModel extends Mysql {
  public function slugsByUserId(int $uid): array {
    return array_column($this->select(
      "SELECT r.slug FROM roles r
       JOIN role_user ru ON ru.role_id=r.id
      WHERE ru.user_id=?",[$uid]), 'slug');
  }
  public function permsByUserId(int $uid): array {
    return array_column($this->select(
      "SELECT DISTINCT p.slug FROM permissions p
         JOIN permission_role pr ON pr.permission_id=p.id
         JOIN role_user ru ON ru.role_id=pr.role_id
        WHERE ru.user_id=?",[$uid]), 'slug');
  }
}
