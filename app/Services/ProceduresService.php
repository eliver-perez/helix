<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\ProceduresRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class ProceduresService extends Service
{
    public function __construct(
        private ProceduresRepository $proceduresRepository
    ) {
    }

    public function getAll(): array {

        try {
            $data = $this->proceduresRepository->getAll();
            $procedures = array();

            foreach($data as $d) {
                array_push($procedures, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'code'                      => $d['codigo'],
                    'procedure'                 => $d['servicio'],
                    'duration'                  => $d['duracion_min'],
                    'base_cost'                 => $d['costo_base'],
                    'material_required'         => $d['requiere_material'],
                    'is_procedure'              => $d['es_procedimiento'],
                    'active'                    => $d['activo'],
                ));
            }

            return $procedures;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getProcedureStaff($procedure): array {

        try {
            $procedureUuid = $this->uuidStringToBinary($procedure);

            $data = $this->proceduresRepository->getProcedureStaff($procedureUuid);
            $procedures = array();

            foreach($data as $d) {
                array_push($procedures, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'name'                      => $d['nombre'],
                    'duration'                  => $d['duracion_min'],
                    'cost'                      => $d['costo'],
                ));
            }

            return $procedures;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getProcedureStaffData($procedure, $staff): array {

        try {
            $procedureUuid = $this->uuidStringToBinary($procedure);
            $staffUuid = $this->uuidStringToBinary($staff);

            $data = $this->proceduresRepository->getProcedureStaffData($procedureUuid, $staffUuid);

            $staff = array('id'                     => $data['id'],
                            'procedimiento_id'      => $this->uuidBinaryToString($data['procedimiento_uuid']),
                            'procedimiento'         => $data['procedimiento'],
                            'personal_id'           => $this->uuidBinaryToString($data['personal_uuid']),
                            'nombre'                => $data['nombre'],
                            'duracion'              => $data['duracion_min'],
                            'costo'                 => $data['costo']);

            return $staff;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}