<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class ConsentTemplatesRepository {
    public function __construct(private PDO $db) {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getAll(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cp.uuid,
                cp.codigo,
                cp.nombre,
                cp.version,
                pe.codigo estatus_codigo,
                pe.estatus,
                u.nombre registro,
                COALESCE(DATE_FORMAT(cp.f_registro, '%d/%m/%Y %r'), '') f_registro
            FROM consentimientos_plantillas cp
                INNER JOIN plantillas_estatus pe
                    ON cp.estatus = pe.id
                INNER JOIN usuarios u
                    ON cp.registro = u.id
            ORDER BY cp.f_registro ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getTemplate($uuid): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                cp.codigo,
                cp.nombre,
                cp.version,
                pe.codigo estatus_codigo,
                pe.estatus,
                u.nombre registro,
                CASE WHEN pe.codigo = 'borrador'
                    THEN cp.delta_borrador
                    ELSE cp.delta_json
                END delta,
                COALESCE(DATE_FORMAT(cp.f_registro, '%d/%m/%Y %r'), '') f_registro,
                COALESCE(DATE_FORMAT(cp.f_actualizacion, '%d/%m/%Y %r'), '') f_actualizacion
            FROM consentimientos_plantillas cp
                INNER JOIN plantillas_estatus pe
                    ON cp.estatus = pe.id
                INNER JOIN usuarios u
                    ON cp.registro = u.id
            WHERE cp.uuid = :uuid
            ORDER BY cp.f_registro ASC
        ");

        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    public function existsById(int $id): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1
            FROM consentimientos_plantillas
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
            FROM consentimientos_plantillas
            WHERE codigo = :code
            LIMIT 1
        ");

        $stmt->execute([
            'code' => $code
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function getStatusIdByCode(string $code): ?int {
        $stmt = $this->db->prepare("
            SELECT id
            FROM plantillas_estatus
            WHERE codigo = :code
            LIMIT 1");
        
        $stmt->execute([
            'code'  => $code
        ]);

        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    public function getTemplateNextVersion(): int {
        $stmt = $this->db->prepare("
            SELECT COALESCE(MAX(version), 0) + 1 AS version
            FROM consentimientos_plantillas
            FOR UPDATE
        ");

        $stmt->execute();

        return (int)$stmt->fetchColumn();
    }

    public function insert(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO consentimientos_plantillas (
                uuid,
                codigo,
                nombre,
                version,
                plantilla,
                estatus,
                registro,
                f_registro,
                f_actualizacion
            ) VALUES (
                :uuid,
                :codigo,
                :nombre,
                0,
                1,
                :estatus,
                :registro,
                NOW(),
                NOW()
            );
        ");

        $stmt->execute([
            'uuid'                  => $data['uuid'],
            'codigo'                => $data['code'],
            'nombre'                => $data['template_name'],
            'estatus'               => $data['status'],
            'registro'              => $data['uid'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function insertTemplateVersion($templateId, $version): void {
        $stmt = $this->db->prepare("UPDATE consentimientos_plantillas 
                                            SET version = :version
                                            WHERE id = :id");

        $stmt->execute([
            'id'                    => $templateId,
            'version'               => $version,
            ]);
    }

    public function update(array $data): void {
        $stmt = $this->db->prepare("UPDATE consentimientos_plantillas
                                            SET documento_borrador = :html,
                                                delta_borrador = :delta
                                            WHERE uuid = :uuid");
        $stmt->bindValue(':html', $data['template_html'], PDO::PARAM_STR);
        $stmt->bindValue(':delta', $data['template_delta'], PDO::PARAM_STR);
        $stmt->bindValue(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->execute();
    }
}