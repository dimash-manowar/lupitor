<?php
class HomeTestimonioModel extends Mysql {
  protected string $tabla = 'home_testimonios';
  public function listarVisibles(): array {
    return $this->select("SELECT * FROM {$this->tabla} WHERE visible=1 ORDER BY orden ASC");
  }
  public function crear(array $d): ?int {
    $sql="INSERT INTO {$this->tabla}(nombre,rol,texto,orden,visible) VALUES(:n,:r,:t,:o,:v)";
    return $this->insert($sql,[
      ':n'=>$d['nombre'],':r'=>$d['rol']?:null,':t'=>$d['texto'],':o'=>(int)($d['orden']??1),':v'=>(int)($d['visible']??1)
    ]);
  }
  public function eliminar(int $id): int { return $this->delete("DELETE FROM {$this->tabla} WHERE id=:id", [':id'=>$id]); }
  public function reordenar(array $ids): int {
    $n=0; $o=1; foreach($ids as $id){ $n+=$this->update("UPDATE {$this->tabla} SET orden=:o WHERE id=:id",[':o'=>$o++,':id'=>(int)$id]); }
    return $n;
  }
}
