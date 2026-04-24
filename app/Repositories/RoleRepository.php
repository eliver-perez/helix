<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class RoleRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                puesto
            FROM puestos
            ORDER BY id ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsById(int $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM puestos
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        return (bool) $stmt->fetchColumn();
    }
}