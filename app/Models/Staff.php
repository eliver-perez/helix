<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Staff extends Model
{
    public function getAll(?string $search = null): array
    {
        $sql = "
            SELECT
                p.id,
                TRIM(
                    p.nombre || ' ' ||
                    COALESCE(p.paterno, '') || ' ' ||
                    COALESCE(p.materno, '')
                ) nombre,
                TRIM(
                    COALESCE(p.calle, '') || ' ' ||
                    COALESCE(p.num_ext, '') || ' ' ||
                    COALESCE(p.num_int, ', ') || ', ' ||
                    COALESCE(c.colonia, ', ') || ', ' ||
                    COALESCE(m.municipio, ', ') || ', ' ||
                    COALESCE(e.estado, '')
                ) nombre,
                p.email,
                p.telefono,
                p.estatus,
                p.f_registro,
                p.f_actualizacion
            FROM personal p
                LEFT JOIN colonias c
                    ON p.colonia = c.id
                LEFT JOIN municipios m
                    ON c.municipio = m.id
                LEFT JOIN estados e
                    ON m.estado = e.id
        ";

        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " AND nombre LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT
                p.id,
                CONCAT(COALESCE(p.nombre, ''),
                        COALESCE(CONCAT(' ', p.paterno), ''),
                        COALESCE(CONCAT(' ', p.materno), '')) nombre,
                CONCAT(COALESCE(p.calle, ''),
                        COALESCE(CONCAT(' ', p.num_ext), ''),
                        COALESCE(CONCAT(', ', p.num_int), ', '),
                        COALESCE(CONCAT(', ', c.colonia), ', '),
                        COALESCE(CONCAT(', ', m.municipio), ', '),
                        COALESCE(e.estado, '')) domicilio,
                p.email,
                p.telefono,
                p.estatus,
                p.f_registro,
                p.f_actualizacion
            FROM personal p
                LEFT JOIN colonias c
                    ON p.colonia = c.id
                LEFT JOIN municipios m
                    ON c.municipio = m.id
                LEFT JOIN estados e
                    ON m.estado = e.id
            WHERE p.id = :id
        ");

        $stmt->execute([
            'id' => $id,
        ]);

        $row = $stmt->fetch();

        return $row ?: null;
    }
}