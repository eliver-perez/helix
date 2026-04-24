<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class BillingRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getRegimenes(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo,
                codigo_sat,
                regimen
            FROM facturacion_regimen
            ORDER BY codigo_sat ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existsRegimenById(int $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM facturacion_regimen
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        return (bool) $stmt->fetchColumn();
    }
}