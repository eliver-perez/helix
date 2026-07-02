<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class CashReconciliationStatusRepository
{
    public function __construct(private PDO $db) {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getIdByCode(string $code): ?int {
        $stmt = $this->db->prepare("
            SELECT id
            FROM cortes_estatus
            WHERE codigo = :code
            LIMIT 1
        ");

        $stmt->execute([
            'code' => $code
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data != null ? $data['id'] : 0;
    }
}