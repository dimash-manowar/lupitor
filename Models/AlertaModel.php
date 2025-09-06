<?php
class AlertaModel extends Mysql
{
    protected string $tabla = 'alertas';

    public function crear(array $d): ?int
    {
        $sql = "INSERT INTO {$this->tabla}
                (titulo, cuerpo, link_url, audiencia, segmento_json, enviar_email, creada_por)
                VALUES (?,?,?,?,?,?,?)";
        return $this->insert($sql, [
            $d['titulo'],
            $d['cuerpo'] ?? null,
            $d['link_url'] ?? null,
            $d['audiencia'],
            $d['segmento_json'] ?? null,
            (int)($d['enviar_email'] ?? 1),
            (int)$d['creada_por'],
        ]);
    }

    public function listar(int $limit=50, int $off=0): array
    {
        $limit = max(1, min(200, $limit));
        $off   = max(0, $off);
        return $this->select("SELECT * FROM {$this->tabla} ORDER BY id DESC LIMIT {$limit} OFFSET {$off}");
    }

    public function ver(int $id): ?array
    {
        return $this->select_one("SELECT * FROM {$this->tabla} WHERE id=? LIMIT 1", [$id]);
    }
}
