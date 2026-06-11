<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class UsersRepository
{
    public function __construct(private PDO $db)
    {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getAll(?string $search = null): array
    {
        $sql = "
            SELECT
                u.id,
                u.usuario,
                u.nombre,
                ut.tipo,
                u.activo,
                COALESCE(DATE_FORMAT(u.f_registro, '%d/%m/%Y %r'), '') f_registro,
                COALESCE(DATE_FORMAT(u.f_ultima_conexion, '%d/%m/%Y %r'), '') f_ultima_conexion
            FROM usuarios u
                LEFT JOIN usuarios_empresas_roles uer
                    ON u.id = uer.usuario
                LEFT JOIN usuarios_tipos ut
                    ON uer.tipo_usuario = ut.id
        ";

        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " AND usuario LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY usuario ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function userExists(string $username): bool {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM usuarios
            WHERE usuario = :usuario
            LIMIT 1
        ");

        $stmt->execute([
            'usuario' => $username
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function insertUser(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO usuarios (
                uuid,
                nombre,
                usuario,
                password_hash,
                activo,
                f_registro
            ) VALUES (
                :uuid,
                :nombre,
                :usuario,
                :password_hash,
                1,
                NOW()
            )
        ");

        $stmt->execute([
            'uuid'                  => $data['uuid'],
            'nombre'                => $data['name'],
            'usuario'               => $data['username'],
            'password_hash'         => $data['password_hash']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function insertStaffUser(int $staffId, int $userId): void {
        $stmt = $this->db->prepare("
            INSERT INTO personal_usuarios (
                personal,
                usuario,
                f_registro
            ) VALUES (
                :personal,
                :usuario,
                NOW()
            )
        ");

        $stmt->execute([
            'personal'              => $staffId,
            'usuario'               => $userId,
        ]);
    }
}