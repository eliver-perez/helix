<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class ConsultationCatalogRepository {
    public function __construct(private PDO $db)
    {
    }

    public function getPodiatryFootTypes(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_pies
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryPulseTypes(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_pulso
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatrySensitivityTypes(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_sensibilidad
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryFootTemperatureTypes(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_temperatura_pie
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryFootColorTypes(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_coloracion_pie
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryMetatarsalFormula(): ?array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                formula
            FROM formula_metatarsal
            ORDER BY formula ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatrySoreType(): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_lesiones
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLaterality(): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                lateralidad
            FROM lateralidades
            ORDER BY lateralidad ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryWagnerScale(): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                grado
            FROM grado_wagner
            ORDER BY grado ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryTissueTypes(): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_tejido
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryEvolution(): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_evolucion
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryExudateTypes(): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                tipo
            FROM tipos_exudado
            ORDER BY tipo ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPodiatryExudateColors(): ?array {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                codigo,
                color
            FROM color_exudado
            ORDER BY color ASC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}