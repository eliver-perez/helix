<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\ConsentTemplatesRepository;
use App\Repositories\TemplatesStatusRepository;
use App\Pdf\Consent\ConsentPdfRenderer;
use InvalidArgumentException;
use RuntimeException;

class ConsentTemplatesService extends Service
{
    public function __construct(
        private ConsentTemplatesRepository $consentTemplatesRepository,
        private TemplatesStatusRepository $templatesStatusRepository
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

    function getTemplateStatus($data): ?array {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de plantilla.'
            );

            $templateUuid = $this->uuidStringToBinary($data['uuid']);

            return $this->consentTemplatesRepository->getTemplateStatus($templateUuid);
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

            $base_path = STORAGE_PATH.'/templates/consents/' . $uuid;
            $this->checkDirectory($base_path);

            $templateStatus = $this->consentTemplatesRepository->getTemplateStatus($templateUuid);
            if($templateStatus['codigo'] != 'borrador')
                throw new RuntimeException("No es posible modificar una plantilla que ya no este en estatus de borrador.");

            $logo = null;
            $actualLogo = $this->consentTemplatesRepository->getTemplateLogo([
                'uuid'          => $templateUuid
                ]);
            if (!$actualLogo) {
                throw new RuntimeException('La plantilla no existe.');
            }

            if (isset($_FILES['file-logo'])) {
                $logo = $this->validateAndSaveLogo($_FILES['file-logo'], $base_path, $actualLogo['logo'] ?? '', $actualLogo['logo_checksum'] ?? '', 'logo_');
            }

            $logo_name = $logo['name'] ?? $actualLogo['logo'];
		    $logo_checksum = $logo['checksum'] ?? $actualLogo['logo_checksum'];

            $this->consentTemplatesRepository->update([
                'template_html'                         => $template_html,
                'template_delta'                        => $template_delta,
                'logo'                                  => $logo_name,
                'logo_checksum'                         => $logo_checksum,
                'uuid'                                  => $templateUuid,
            ]);
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    public function previewPdf(array $data): string {
        $clinicName = $this->consentTemplatesRepository->getClinicName();

        $data['clinic_name'] = $clinicName;

        $renderer = new ConsentPdfRenderer();

        return $renderer->renderPreview($data);
    }


    protected function checkDirectory(string $path): void {
        if (!is_dir($path)) {
            if (!mkdir($path, 0775, true) && !is_dir($path)) {
                throw new RuntimeException('No fue posible crear el directorio.');
            }
        }
    }

    protected function generateFilename(string $extension, string $prefix = 'logo_'): string {
        return $prefix . bin2hex(random_bytes(16)) . '.' . $extension;
    }

    protected function validateAndSaveLogo(array $file, string $destinationPath, string $actualLogo, string $checksum, string $prefix = 'logo_'): ?array {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Error al subir el archivo.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            throw new RuntimeException('El archivo recibido no es válido.');
        }
        
        $hash_md5 = md5_file($file['tmp_name']);
        if($hash_md5 == $checksum) {
            return [
                'nombre' => $actualLogo,
                'checksum' => $checksum
            ];
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedExtensions = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp'
        ];

        if (!isset($allowedExtensions[$mime])) {
            throw new RuntimeException('El archivo debe ser una imagen JPG, PNG o WEBP.');
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            throw new RuntimeException('El archivo no es una imagen válida.');
        }

        $extension = $allowedExtensions[$mime];
        $generatedFilename = $this->generateFilename($extension, $prefix);
        $globalPath = rtrim($destinationPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $generatedFilename;

        if (!move_uploaded_file($file['tmp_name'], $globalPath)) {
            throw new RuntimeException('No fue posible guardar la imagen.');
        }

        return [
            'name' => $generatedFilename,
            'mime' => $mime,
            'size' => filesize($globalPath),
            'width' => $imageInfo[0] ?? null,
            'height' => $imageInfo[1] ?? null,
            'global_path' => $globalPath,
            'checksum' => $hash_md5
        ];
    }

    function activate($data): void {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de plantilla.'
            );

            $templateUuid = $this->uuidStringToBinary($data['uuid']);
            
            $conn = $this->consentTemplatesRepository->getConnection();
            $conn->beginTransaction();

            $draftStatusId = $this->templatesStatusRepository->getIdByCode('borrador');
            $activeStatusId = $this->templatesStatusRepository->getIdByCode('activo');
            $inactiveStatusId = $this->templatesStatusRepository->getIdByCode('inactivo');

            $this->consentTemplatesRepository->deactivateAll([
                'inactive_id'                           => $inactiveStatusId,
                'active_id'                             => $activeStatusId
            ]);

            $this->consentTemplatesRepository->activate([
                'uuid'                                  => $templateUuid,
                'active_id'                             => $activeStatusId,
            ]);

            $conn->commit();
        } catch(\Throwable $e) {
            $conn->rollBack();
            throw $e;
        }
    }
}