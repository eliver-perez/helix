<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class CashRegisterRepository
{
    public function __construct(private PDO $db) {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                uuid,
                codigo,
                caja
            FROM cajas
            ORDER BY id ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIdByUuid($uuid): ?int {
        $stmt = $this->db->prepare("
            SELECT id
            FROM cajas
            WHERE uuid = :uuid
            LIMIT 1
        ");
        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data != null ? $data['id'] : 0;
    }
}