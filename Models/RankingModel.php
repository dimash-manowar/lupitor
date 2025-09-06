<?php
class RankingModel extends Mysql
{
    private string $t = 'ranking';


    public function top(string $temporada, int $lim = 5): array
    {
        $lim = max(1, (int)$lim);
        // Inyectamos el LIMIT ya validado para evitar problemas con emulación desactivada
        $sql = "SELECT * FROM {$this->t}
                WHERE temporada = :temporada
                ORDER BY posicion ASC, puntos DESC, elo DESC
                LIMIT {$lim}";
        $st = $this->conect->prepare($sql);
        $st->bindValue(':temporada', $temporada, PDO::PARAM_STR);
        $st->execute();
        return $st->fetchAll();
    }

    public function tabla(string $temporada, int $page = 1, int $per = 25): array
    {
        $page = max(1, (int)$page);
        $per  = max(1, (int)$per);
        $off  = ($page - 1) * $per;

        $sql = "SELECT SQL_CALC_FOUND_ROWS *
                FROM {$this->t}
                WHERE temporada = :temporada
                ORDER BY posicion ASC
                LIMIT {$per} OFFSET {$off}";
        $st = $this->conect->prepare($sql);
        $st->bindValue(':temporada', $temporada, PDO::PARAM_STR);
        $st->execute();

        $rows  = $st->fetchAll();
        $total = (int)$this->conect->query('SELECT FOUND_ROWS()')->fetchColumn();

        return ['rows' => $rows, 'meta' => ['page' => $page, 'per' => $per, 'total' => $total]];
    }



    public function upsert(array $r): bool
    {
        $sql = "INSERT INTO {$this->t}(temporada,jugador,club,elo,puntos,posicion) VALUES(?,?,?,?,?,?)
ON DUPLICATE KEY UPDATE club=VALUES(club), elo=VALUES(elo), puntos=VALUES(puntos), posicion=VALUES(posicion)";
        $st = $this->conect->prepare($sql);
        return $st->execute([$r['temporada'], $r['jugador'], $r['club'] ?? 'Berriozar', $r['elo'] ?? 0, $r['puntos'] ?? 0, $r['posicion'] ?? 0]);
    }


    public function clearSeason(string $temporada): int
    {
        return $this->delete("DELETE FROM {$this->t} WHERE temporada=?", [$temporada]);
    }
}
