<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class UsersTypesRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getAll(): array {
        $sql = "
            SELECT
                ut.id,
                ut.codigo,
                ut.tipo
            FROM usuarios_tipos ut
            ORDER BY ut.tipo
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}