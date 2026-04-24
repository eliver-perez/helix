<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class LocationRepository
{
    public function __construct(private PDO $db)
    {
    }

    /* =========================
     * COUNTRIES
     * ========================= */

    public function findCountries(?string $search = null): array
    {
        $sql = "SELECT id, pais, abbr FROM paises WHERE 1=1";
        $params = [];

        if ($search !== null && $search !== '') {
            $sql .= " AND pais LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY pais ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countryExists(int $countryId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM paises WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $countryId]);

        return (bool) $stmt->fetchColumn();
    }

    /* =========================
     * STATES
     * ========================= */

    public function findStates(?int $countryId = null, ?string $search = null): array
    {
        $sql = "SELECT id, estado FROM estados WHERE 1=1";
        $params = [];

        if ($countryId !== null) {
            $sql .= " AND pais = :pais";
            $params['pais'] = $countryId;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND estado LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY estado ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function stateExists(int $stateId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM estados WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $stateId]);

        return (bool) $stmt->fetchColumn();
    }

    public function stateExistsByName(int $countryId, string $name): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM estados 
            WHERE pais = :pais 
              AND estado = :estado 
            LIMIT 1
        ");

        $stmt->execute([
            'pais'      => $countryId,
            'estado'    => $name
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function insertState(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO estados (estado, pais)
            VALUES (:estado, :pais)
        ");

        $stmt->execute([
            'estado'    => $data['estado'],
            'pais'      => $data['pais']
        ]);

        return (int) $this->db->lastInsertId();
    }

    /* =========================
     * MUNICIPALITIES
     * ========================= */

    public function findMunicipalities(?int $stateId = null, ?string $search = null): array
    {
        $sql = "SELECT id, municipio, estado FROM municipios WHERE 1=1";
        $params = [];

        if ($stateId !== null) {
            $sql .= " AND estado = :estado";
            $params['estado'] = $stateId;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND municipio LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY municipio ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function municipalityExists(int $municipalityId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM municipios WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $municipalityId]);

        return (bool) $stmt->fetchColumn();
    }

    public function municipalityExistsByName(int $stateId, string $name): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM municipios 
            WHERE estado = :estado 
              AND municipio = :municipio 
            LIMIT 1
        ");

        $stmt->execute([
            'estado'        => $stateId,
            'municipio'     => $name
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function insertMunicipality(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO municipios (municipio, estado)
            VALUES (:municipio, :estado)
        ");

        $stmt->execute([
            'municipio'   => $data['municipio'],
            'estado'   => $data['estado']
        ]);

        return (int) $this->db->lastInsertId();
    }

    /* =========================
     * LOCALITIES
     * ========================= */

    public function findLocalities(?int $municipalityId = null, ?string $search = null): array
    {
        $sql = "SELECT id, colonia, cp FROM colonias WHERE 1=1";
        $params = [];

        if ($municipalityId !== null) {
            $sql .= " AND municipio = :municipio";
            $params['municipio'] = $municipalityId;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND colonia LIKE :search";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY colonia ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function localityExists(int $localityId): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM colonias WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $localityId]);

        return (bool) $stmt->fetchColumn();
    }

    public function localityExistsByName(int $municipalityId, string $name): bool
    {
        $stmt = $this->db->prepare("
            SELECT 1 
            FROM colonias 
            WHERE municipio = :municipio 
              AND colonia = :colonia 
            LIMIT 1
        ");

        $stmt->execute([
            'municipio'     => $municipalityId,
            'colonia'       => $name
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function insertLocality(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO colonias (colonia, municipio, codigo_postal)
            VALUES (:colonia, :municipio, :codigo_postal)
        ");

        $stmt->execute([
            'colonia'           => $data['colonia'],
            'municipio'         => $data['municipio'],
            'codigo_postal'     => $data['codigo_postal']
        ]);

        return (int) $this->db->lastInsertId();
    }
}