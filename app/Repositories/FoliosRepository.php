<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class FoliosRepository {
    public function __construct(private PDO $db)
    {
    }

    public function getConsecutive($type, $year): ?int {
        $stmt = $this->db->prepare("
            SELECT consecutivo
            FROM folios_consecutivos
            WHERE tipo = :type
            AND ejercicio = :year
            FOR UPDATE
        ");

        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':year', $year);
        $stmt->execute();

        return $stmt->fetchColumn();
    }
    
    public function updateConsecutive($type, $year, $consecutive) {
        try {
            $sql = "
                UPDATE folios_consecutivos SET
                    consecutivo = :consecutive
                WHERE tipo = :type
                AND ejercicio = :year
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':consecutive', $consecutive);
            $stmt->bindValue(':type', $type);
            $stmt->bindValue(':year', $year);

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch(Exception $ex) {
            throw $ex;
        }
    }
}