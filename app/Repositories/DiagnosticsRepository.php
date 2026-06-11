<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class DiagnosticsRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getDiagnostics(): array {
        $stmt = $this->db->prepare("
            SELECT 
                d.id,
                d.uuid,
                d.codigo,
                d.diagnostico,
                dc.codigo categoria_codigo,
                dc.categoria,
                e.codigo especialidad_codigo,
                e.especialidad
            FROM diagnosticos d
                INNER JOIN diagnosticos_categorias dc
                    ON d.categoria = dc.id
                INNER JOIN especialidades e
                    ON d.especialidad = e.id
            ORDER BY id ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDiagnosticTypes(): array {
        $stmt = $this->db->prepare("
            SELECT 
                dt.id,
                dt.codigo,
                dt.tipo
            FROM diagnosticos_tipos dt
            ORDER BY dt.tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getDiagnosticName($uuid): ?string {
        $stmt = $this->db->prepare("
            SELECT d.diagnostico
            FROM diagnosticos d
            WHERE d.uuid = :uuid
        ");
        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getDiagnosticId(string $uuid): ?int {
        $stmt = $this->db->prepare("
            SELECT id
            FROM diagnosticos
            WHERE uuid = :uuid
            LIMIT 1");
        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    public function existsById(string $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM diagnosticos
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        return (bool) $stmt->fetchColumn();
    }
}