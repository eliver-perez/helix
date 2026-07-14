<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class UnitsMeasureRepository
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
                unidad
            FROM unidades
            ORDER BY unidad ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsById(string $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM unidades
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function existsByCode(string $code): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM unidades
            WHERE codigo = :code
            LIMIT 1
        ");

        $stmt->execute([
            'code' => $code
        ]);

        return (bool) $stmt->fetchColumn();
    }
}