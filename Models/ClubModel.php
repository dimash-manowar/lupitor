<?php
class ClubModel extends Mysql
{
    protected string $table = 'club_info';

    public function get(): ?array {
        return $this->select_one("SELECT * FROM {$this->table} WHERE id=1");
    }

    public function ensureRow(): void {
        $row = $this->select_one("SELECT id FROM {$this->table} WHERE id=1");
        if (!$row) {
            $this->insert("INSERT INTO {$this->table}(id,titulo,created_at,updated_at)
                           VALUES (1,'Club de Ajedrez de Berriozar',NOW(),NOW())");
        }
    }

    public function actualizarInfo(array $d): int {
        $horarios = !empty($d['horarios'])
            ? (is_array($d['horarios']) ? json_encode($d['horarios'], JSON_UNESCAPED_UNICODE) : (string)$d['horarios'])
            : null;

        $sql = "UPDATE {$this->table}
                   SET titulo      = ?,
                       subtitulo   = ?,
                       cuerpo_html = ?,
                       direccion   = ?,
                       email       = ?,
                       telefono    = ?,
                       horarios    = ?,
                       mapa_iframe = ?,
                       portada     = ?,
                       updated_at  = NOW()
                 WHERE id = 1";

        return parent::update($sql, [
            $d['titulo']      ?? '',
            $d['subtitulo']   ?? null,
            $d['cuerpo_html'] ?? null,
            $d['direccion']   ?? null,
            $d['email']       ?? null,
            $d['telefono']    ?? null,
            $horarios,
            $d['mapa_iframe'] ?? null,
            $d['portada']     ?? null,
        ]);
    }

    public function upsertInfo(array $d): int {
        $horarios = !empty($d['horarios'])
            ? (is_array($d['horarios']) ? json_encode($d['horarios'], JSON_UNESCAPED_UNICODE) : (string)$d['horarios'])
            : null;

        $sql = "INSERT INTO {$this->table}
                    (id,titulo,subtitulo,cuerpo_html,direccion,email,telefono,horarios,mapa_iframe,portada,created_at,updated_at)
                VALUES
                    (1,?,?,?,?,?,?,?,?,?,NOW(),NOW())
                ON DUPLICATE KEY UPDATE
                    titulo=VALUES(titulo),
                    subtitulo=VALUES(subtitulo),
                    cuerpo_html=VALUES(cuerpo_html),
                    direccion=VALUES(direccion),
                    email=VALUES(email),
                    telefono=VALUES(telefono),
                    horarios=VALUES(horarios),
                    mapa_iframe=VALUES(mapa_iframe),
                    portada=VALUES(portada),
                    updated_at=NOW()";

        return parent::update($sql, [
            $d['titulo']      ?? '',
            $d['subtitulo']   ?? null,
            $d['cuerpo_html'] ?? null,
            $d['direccion']   ?? null,
            $d['email']       ?? null,
            $d['telefono']    ?? null,
            $horarios,
            $d['mapa_iframe'] ?? null,
            $d['portada']     ?? null,
        ]);
    }
}
