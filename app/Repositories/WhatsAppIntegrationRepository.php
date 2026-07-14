<?php

namespace App\Repositories;

use JsonException;
use PDO;
use RuntimeException;

final readonly class WhatsAppIntegrationRepository
{
    public function __construct(
        private PDO $database
    ) {
    }

    /**
     * Obtiene la integración de WhatsApp de una empresa,
     * independientemente de que esté activa o inactiva.
     */
    public function findByCompany(int $companyId): ?array
    {
        $statement = $this->database->prepare(
            'SELECT
                id,
                BIN_TO_UUID(uuid) AS uuid,
                empresa,
                proveedor,
                nombre,
                configuracion,
                credenciales,
                activo,
                ultima_prueba_at,
                ultima_prueba_exitosa,
                ultimo_error,
                registrado_por,
                actualizado_por,
                f_registro,
                f_actualizacion
            FROM integraciones_whatsapp
            WHERE empresa = :empresa
            LIMIT 1'
        );

        $statement->execute([
            'empresa' => $companyId,
        ]);

        $integration = $statement->fetch(PDO::FETCH_ASSOC);

        if ($integration === false) {
            return null;
        }

        return $this->hydrate($integration);
    }

    /**
     * Obtiene únicamente la integración activa.
     * Este será el método utilizado al enviar mensajes.
     */
    public function findActiveByCompany(int $companyId): ?array
    {
        $statement = $this->database->prepare(
            'SELECT
                id,
                BIN_TO_UUID(uuid) AS uuid,
                empresa,
                proveedor,
                nombre,
                configuracion,
                credenciales,
                activo,
                ultima_prueba_at,
                ultima_prueba_exitosa,
                ultimo_error,
                registrado_por,
                actualizado_por,
                f_registro,
                f_actualizacion
            FROM integraciones_whatsapp
            WHERE empresa = :empresa
              AND activo = 1
            LIMIT 1'
        );

        $statement->execute([
            'empresa' => $companyId,
        ]);

        $integration = $statement->fetch(PDO::FETCH_ASSOC);

        if ($integration === false) {
            return null;
        }

        return $this->hydrate($integration);
    }

    /**
     * Inserta o actualiza la integración de una empresa.
     *
     * $credentials debe recibirse ya cifrado.
     */
    public function save(
        int $companyId,
        string $provider,
        string $name,
        array $configuration,
        string $credentials,
        bool $active,
        int $userId
    ): int {
        $integration = $this->findByCompany($companyId);

        if ($integration === null) {
            return $this->insert(
                companyId: $companyId,
                provider: $provider,
                name: $name,
                configuration: $configuration,
                credentials: $credentials,
                active: $active,
                userId: $userId
            );
        }

        $this->update(
            integrationId: $integration['id'],
            provider: $provider,
            name: $name,
            configuration: $configuration,
            credentials: $credentials,
            active: $active,
            userId: $userId
        );

        return $integration['id'];
    }

    /**
     * Guarda el resultado de una prueba de conexión o envío.
     */
    public function updateTestResult(
        int $integrationId,
        bool $successful,
        ?string $error,
        int $userId
    ): void {
        $statement = $this->database->prepare(
            'UPDATE integraciones_whatsapp
             SET ultima_prueba_at = CURRENT_TIMESTAMP,
                 ultima_prueba_exitosa = :exitosa,
                 ultimo_error = :error,
                 actualizado_por = :actualizado_por
             WHERE id = :id'
        );

        $statement->execute([
            'exitosa'         => $successful ? 1 : 0,
            'error'           => $successful ? null : $error,
            'actualizado_por' => $userId,
            'id'              => $integrationId,
        ]);
    }

    private function insert(
        int $companyId,
        string $provider,
        string $name,
        array $configuration,
        string $credentials,
        bool $active,
        int $userId
    ): int {
        $statement = $this->database->prepare(
            'INSERT INTO integraciones_whatsapp (
                uuid,
                empresa,
                proveedor,
                nombre,
                configuracion,
                credenciales,
                activo,
                registrado_por,
                actualizado_por
            ) VALUES (
                UUID_TO_BIN(UUID()),
                :empresa,
                :proveedor,
                :nombre,
                :configuracion,
                :credenciales,
                :activo,
                :registrado_por,
                :actualizado_por
            )'
        );

        $statement->execute([
            'empresa'         => $companyId,
            'proveedor'       => $provider,
            'nombre'          => $name,
            'configuracion'   => $this->encodeJson($configuration),
            'credenciales'    => $credentials,
            'activo'          => $active ? 1 : 0,
            'registrado_por'  => $userId,
            'actualizado_por' => $userId,
        ]);

        return (int) $this->database->lastInsertId();
    }

    private function update(
        int $integrationId,
        string $provider,
        string $name,
        array $configuration,
        string $credentials,
        bool $active,
        int $userId
    ): void {
        $statement = $this->database->prepare(
            'UPDATE integraciones_whatsapp
             SET proveedor = :proveedor,
                 nombre = :nombre,
                 configuracion = :configuracion,
                 credenciales = :credenciales,
                 activo = :activo,

                 ultima_prueba_at = NULL,
                 ultima_prueba_exitosa = NULL,
                 ultimo_error = NULL,

                 actualizado_por = :actualizado_por
             WHERE id = :id'
        );

        $statement->execute([
            'proveedor'       => $provider,
            'nombre'          => $name,
            'configuracion'   => $this->encodeJson($configuration),
            'credenciales'    => $credentials,
            'activo'          => $active ? 1 : 0,
            'actualizado_por' => $userId,
            'id'              => $integrationId,
        ]);
    }

    private function hydrate(array $integration): array
    {
        try {
            $configuration = json_decode(
                $integration['configuracion'],
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'La configuración de la integración de WhatsApp no contiene un JSON válido.',
                previous: $exception
            );
        }

        if (!is_array($configuration)) {
            throw new RuntimeException(
                'La configuración de la integración de WhatsApp no es válida.'
            );
        }

        $integration['id'] = (int) $integration['id'];
        $integration['empresa'] = (int) $integration['empresa'];
        $integration['activo'] = (bool) $integration['activo'];
        $integration['configuracion'] = $configuration;

        $integration['ultima_prueba_exitosa'] =
            $integration['ultima_prueba_exitosa'] === null
                ? null
                : (bool) $integration['ultima_prueba_exitosa'];

        return $integration;
    }

    private function encodeJson(array $data): string
    {
        try {
            return json_encode(
                $data,
                JSON_THROW_ON_ERROR |
                JSON_UNESCAPED_UNICODE |
                JSON_UNESCAPED_SLASHES
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'No fue posible convertir la configuración de WhatsApp a JSON.',
                previous: $exception
            );
        }
    }
}