<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class ProceduresRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                servicio
            FROM servicios
            ORDER BY id ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProcedureStaff($procedure): array {
        $stmt = $this->db->prepare("
           SELECT p.id,
                TRIM(
                    CONCAT(
                        p.nombre, ' ',
                        COALESCE(p.paterno, ''), ' ',
                        COALESCE(p.materno, '')
                    )
                ) nombre,
				ps.costo,
				s.duracion_min
				FROM servicios s
					INNER JOIN personal_servicios ps
						ON s.id = ps.servicio
					INNER JOIN personal p
						ON ps.personal = p.id
				WHERE s.id = :procedure
        ");

        $stmt->execute(['procedure' => $procedure]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProcedureStaffData($procedure, $staff): array {
        $stmt = $this->db->prepare("
           SELECT ps.id,
                p.id personal_id,
                TRIM(
                    CONCAT(
                        p.nombre, ' ',
                        COALESCE(p.paterno, ''), ' ',
                        COALESCE(p.materno, '')
                    )
                ) nombre,
                s.id procedimiento_id,
                s.servicio procedimiento,
				ps.costo,
				s.duracion_min
				FROM servicios s
					INNER JOIN personal_servicios ps
						ON s.id = ps.servicio
					INNER JOIN personal p
						ON ps.personal = p.id
				WHERE ps.servicio = :procedure
                    AND ps.personal = :staff
                    AND ps.f_baja IS NULL
                ORDER BY ps.f_registro DESC
                LIMIT 1
        ");

        $stmt->execute([
            'procedure' => $procedure,
            'staff' => $staff
        ]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?? null;
    }

    public function existsById(string $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM servicios
            WHERE id = :id
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id
        ]);

        return (bool) $stmt->fetchColumn();
    }
}