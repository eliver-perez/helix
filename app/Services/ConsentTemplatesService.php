<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\ConsentTemplatesRepository;
use InvalidArgumentException;
use RuntimeException;

class ConsentTemplatesService extends Service
{
    public function __construct(
        private ConsentTemplatesRepository $consentTemplatesRepository
    ) {
    }

    public function getAllTemplates(): ?array {
        $data = $this->consentTemplatesRepository->getAll();

        $templates = array();
        foreach($data as $d) {
            array_push($templates, [
                'id'                      => $this->uuidBinaryToString($d['uuid']),
                'codigo'                    => $d['codigo'],
                'template_name'             => $d['nombre'],
                'version'                   => $d['version'],
                'status_code'               => $d['estatus_codigo'],
                'status'                    => $d['estatus'],
                'registered_by'             => $d['registro'],
                'registered_date'           => $d['f_registro'],
            ]);
        }

        return $templates;
    }

    public function create(array $data): ?string {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');

        $code = $this->normalizeRequiredText(
            $data['code'] ?? null,
            'El codigo es obligatorio.'
        );

        $templateName = $this->normalizeRequiredText(
            $data['template_name'] ?? null,
            'El nombre de la plantilla es obligatorio.'
        );

        $status = $this->consentTemplatesRepository->getStatusIdByCode('borrador');
        if($status === null) {
            throw new InvalidArgumentException('Ocurrio un error al intentar obtener el estatus de registro.');
        }

        if ($this->consentTemplatesRepository->existsByCode($code)) {
            throw new InvalidArgumentException('El código capturado ya se encuentra en uso.');
        }

        $conn = $this->consentTemplatesRepository->getConnection();
        $conn->beginTransaction();
        try {
            $templateUuid = $this->generateUuidBinary();
            $templateId = $this->consentTemplatesRepository->insert([
                    'uuid'                          => $templateUuid,
                    'code'                          => $code,
                    'template_name'                 => $templateName,
                    'status'                        => $status,
                    'uid'                           => $uid,
                ]);
            
            $version = $this->consentTemplatesRepository->getTemplateNextVersion();

            $this->consentTemplatesRepository->insertTemplateVersion($templateId, $version);
            
            $conn->commit();
            
            return $templateUuid;
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    function getTemplate($data): ?array {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de plantilla.'
            );

            $templateUuid = $this->uuidStringToBinary($data['uuid']);

            return $this->consentTemplatesRepository->getTemplate($templateUuid);
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function update($data): void {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de plantilla.'
            );

            $template_html = $this->normalizeRequiredText(
                $data['template_html'] ?? null,
                'Error al recibir datos de plantilla.'
            );

            $template_delta = $this->normalizeRequiredText(
                $data['template_delta'] ?? null,
                'Error al recibir datos de plantilla.'
            );

            $templateUuid = $this->uuidStringToBinary($data['uuid']);

            $this->consentTemplatesRepository->update([
                'template_html'                         => $template_html,
                'template_delta'                        => $template_delta,
                'uuid'                                  => $templateUuid,
            ]);
        } catch(\Throwable $e) {
            throw $e;
        }
    }
}