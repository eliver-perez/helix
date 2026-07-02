<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\ConsultationsRepository;
use App\Repositories\SalesRepository;
use App\Repositories\SalesStatusRepository;
use App\Repositories\AppointmentsRepository;
use App\Repositories\AppointmentsStatusRepository;
use App\Repositories\DiagnosticsRepository;
use App\Repositories\ProceduresRepository;
use App\Repositories\PatientsRepository;
use App\Repositories\GenderRepository;
use App\Repositories\LocationRepository;
use App\Repositories\FoliosRepository;
use App\Repositories\SettingsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class ConsultationsService extends Service
{
    public function __construct(
        private ConsultationsRepository $consultationsRepository,
        private SalesRepository $salesRepository,
        private SalesStatusRepository $salesStatusRepository,
        private AppointmentsRepository $appointmentsRepository,
        private AppointmentsStatusRepository $appointmentsStatusRepository,
        private DiagnosticsRepository $diagnosticsRepository,
        private ProceduresRepository $proceduresRepository,
        private PatientsRepository $patientsRepository,
        private GenderRepository $genderRepository,
        private LocationRepository $locationRepository,
        private FoliosRepository $foliosRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAll(array $data): array {
        try {
            $status_id = $this->appointmentsStatusRepository->getBlockIdByCode($data['status']);

            // throw new RuntimeException("StatusID: ".$data['status'].": ".$status_id);
            // throw new RuntimeException("UID: ".$data['uid']);

            $data = $this->consultationsRepository->getAll([
                'status'            => $status_id,
                'search'            => $data['search'] !== '' ? $data['search'] : null,
                'limit'             => $data['limit'],
                'offset'            => $data['offset'],
                'uid'               => $data['uid'],
            ]);
            $consultations = array();

            foreach($data as $d) {
                array_push($consultations, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'folio'                     => $d['folio'],
                    'patient'                   => $d['paciente'],
                    'age'                       => $d['f_nacimiento'] ? $this->calculateAge($d['f_nacimiento']) : '',
                    'appointment_type'          => $d['asunto'],
                    'date'                      => $d['fecha_cita'],
                    'time_start'                => $this->formatTimeTo12h($this->minutesToTime($d['h_inicio'])),
                    'duration'                  => $d['duracion'],
                    'status_id'                 => $d['estatus_codigo'],
                    'status'                    => $d['estatus'],
                ));
            }

            return $consultations;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function getConsultation($data): ?array {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $conn = $this->consultationsRepository->getConnection();
            $conn->beginTransaction();

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $appointment_uuid = $this->consultationsRepository->getAppointmentUuidByBlock($appointment_block_uuid);
            $appointment_id = $this->appointmentsRepository->getAppointmentId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByAppointment($appointment_uuid);

            $procedures_data = $this->consultationsRepository->getAppointmentProcedures($appointment_uuid, $data['uid']);

            if(!$consultation_uuid) {
                $appointment_status = $this->appointmentsRepository->appointmentStatus($appointment_uuid);
                if($appointment_status == 'en_espera' || $appointment_status == 'agendada') {
                    $consultation_uuid = $this->generateUuidBinary();

                    $startedStatus = $this->normalizeRequiredInt(
                        $this->appointmentsStatusRepository->getIdByCode('en_proceso') ?? null,
                        'Ocurrio un error al intentar obtener información.'
                    );
                    $blockStartedStatus = $this->normalizeRequiredInt(
                        $this->appointmentsStatusRepository->getBlockIdByCode('en_proceso') ?? null,
                        'Ocurrio un error al intentar obtener información.'
                    );

                    $this->consultationsRepository->startConsultation([
                        'uuid'                  => $consultation_uuid,
                        'uid'                   => $data['uid'],
                        'appointment_block_id'  => $appointment_block_id,
                    ]);
                    $this->appointmentsRepository->changeAppointmentStatus([
                        'appointment'           => $appointment_uuid,
                        'status'                => $startedStatus,
                    ]);
                    $this->appointmentsRepository->changeAppointmentBlockStatusByUuid([
                        'block'                 => $appointment_block_uuid,
                        'status'                => $blockStartedStatus,
                    ]);

                    foreach($procedures_data as $pd) {
                        $consultation_procedure_uuid = $this->generateUuidBinary();
                        $this->consultationsRepository->startConsultationProcedure([
                            'uuid'                  => $consultation_procedure_uuid,
                            'consultation_uuid'     => $consultation_uuid,
                            'origin'                => 'agendado',
                            'chargeable'            => 1,
                            'service_id'            => $pd['id'],
                            'uid'                   => $data['uid'],
                        ]);
                    }
                }
            }

            $consultation_data = $this->consultationsRepository->getConsultation($appointment_block_uuid);
            $procedures = array();

            if(!$consultation_data)
                throw new RuntimeException("Ocurrio un error al intentar obtener la información.");
            
            $time_end = 0;
            $modules = array();
            foreach($procedures_data as $sd) {
                if($time_end == 0)
                    $time_end = $sd['h_fin'];
                else if($sd['h_inicio' != $time_end])
                    break;
                $m_data = $this->proceduresRepository->getProcedureEnabledModules($sd['uuid']);
                $m_found = false;
                foreach($m_data as $md) {
                    $m_found = false;
                    foreach($modules as $m) {
                        if($m['code'] == $md['codigo']) {
                            $m_found = true;
                            break;
                        }
                    }
                    if(!$m_found) {
                        array_push($modules, array(
                            'uuid'                  => $this->uuidBinaryToString($md['uuid']),
                            'code'                  => $md['codigo'],
                            'name'                  => $md['nombre'],
                            'description'           => $md['descripcion'],
                            'order_default'         => $md['orden_default'],
                        ));
                    }
                }
                array_push($procedures, array(
                    'id'                        => $this->uuidBinaryToString($sd['uuid']),
                    'code'                      => $sd['codigo'],
                    'procedure'                 => $sd['servicio'],
                    'duration'                  => $sd['duracion'],
                    'time_start'                => $this->formatTimeTo12h($this->minutesToTime($sd['h_inicio'])),
                    'time_end'                  => $this->formatTimeTo12h($this->minutesToTime($sd['h_fin'])),
                ));
            }

            $consultation = array(
                    'id'                        => $this->uuidBinaryToString($consultation_data['uuid']),
                    'folio'                     => $consultation_data['folio'],
                    'patient'                   => $consultation_data['paciente'],
                    'patient_code'              => $consultation_data['paciente_clave'],
                    'phone'                     => $consultation_data['telefono'],
                    'mobile'                    => $consultation_data['movil'],
                    'email'                     => $consultation_data['email'],
                    'gender'                    => $consultation_data['genero'],
                    'current_medications'       => $consultation_data['medicamentos'],
                    'supplements'               => $consultation_data['suplementos'],
                    'family_medical_history'    => $consultation_data['antecedentes_familiares'],
                    'general_observations'      => $consultation_data['observaciones_generales'],
                    'age'                       => $consultation_data['f_nacimiento'] ? $this->calculateAge($consultation_data['f_nacimiento']) : '',
                    'appointment_type'          => $consultation_data['asunto'],
                    'chief_complaint'           => $consultation_data['motivo_consulta'],
                    'initial_observations'      => $consultation_data['observacion_inicial'],
                    'indications'               => $consultation_data['indicaciones'],
                    'diagnostic_summary'        => $consultation_data['diagnostico_resumen'],
                    'date'                      => $consultation_data['fecha_cita'],
                    'time_start'                => $this->formatTimeTo12h($this->minutesToTime($consultation_data['h_inicio'])),
                    'duration'                  => $consultation_data['duracion'],
                    'status_id'                 => $consultation_data['estatus_codigo'],
                    'status'                    => $consultation_data['estatus'],
                    'procedures'                => $procedures,
                    'modules'                   => $modules
            );

            $conn->commit();

            return $consultation;
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    function updateConsultationInitialObservations($data): void {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $observations = $this->normalizeRequiredText(
                $data['observations'] ?? "",
                'Error al recibir observaciones iniciales de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $conn = $this->consultationsRepository->getConnection();
            $conn->beginTransaction();

            $this->consultationsRepository->updateConsultationInitialObservations([
                'uuid'                          => $consultation_uuid,
                'observations'                  => $data['observations'],
            ]);

            $conn->commit();
        } catch(\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    function updateConsultationPodiatricExploration($data): void {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $podiatric_exploration_uuid = $this->consultationsRepository->getPodiatricExplorationUuid($consultation_uuid);

            $foot_type = $this->normalizeOptionalInt(intval($data['foot_type']) ?? 0);
            $metatarsal_formula = $this->normalizeOptionalInt(intval($data['metatarsal_formula']) ?? 0);
            $gait_disorder = $this->normalizeOptionalText(trim($data['gait_disorder'] ?? ''));
            $left_pulse_type = $this->normalizeOptionalInt(intval($data['left_pulse_type']) ?? 0);
            $right_pulse_type = $this->normalizeOptionalInt(intval($data['right_pulse_type']) ?? 0);
            $left_sensitivity_type = $this->normalizeOptionalInt(intval($data['left_sensitivity_type']) ?? 0);
            $right_sensitivity_type = $this->normalizeOptionalInt(intval($data['right_sensitivity_type']) ?? 0);
            $temperature_type = $this->normalizeOptionalInt(intval($data['temperature_type']) ?? 0);
            $foot_color_type = $this->normalizeOptionalInt(intval($data['foot_color_type']) ?? 0);
            $observations = $this->normalizeOptionalText(trim($data['observations'] ?? ''));
            $advice = $this->normalizeOptionalText(trim($data['advice'] ?? ''));

            $foot_type = $foot_type != 0 ? $foot_type : null;
            $metatarsal_formula = $metatarsal_formula != 0 ? $metatarsal_formula : null;
            $gait_disorder = $gait_disorder != '' ? $gait_disorder : null;
            $left_pulse_type = $left_pulse_type != 0 ? $left_pulse_type : null;
            $right_pulse_type = $right_pulse_type != 0 ? $right_pulse_type : null;
            $left_sensitivity_type = $left_sensitivity_type != 0 ? $left_sensitivity_type : null;
            $right_sensitivity_type = $right_sensitivity_type != 0 ? $right_sensitivity_type : null;
            $temperature_type = $temperature_type != 0 ? $temperature_type : null;
            $foot_color_type = $foot_color_type != 0 ? $foot_color_type : null;
            $observations = $observations != '' ? $observations : null;
            $advice = $advice != '' ? $advice : null;

            if($podiatric_exploration_uuid == null) {
                $podiatric_exploration_uuid = $this->generateUuidBinary();

                $this->consultationsRepository->insertConsultationPodiatricExploration([
                    'uuid'                              => $podiatric_exploration_uuid,
                    'consultation_uuid'                 => $consultation_uuid,
                    'foot_type'                         => $foot_type,
                    'metatarsal_formula'                => $metatarsal_formula,
                    'gait_disorder'                     => $gait_disorder,
                    'left_pulse_type'                   => $left_pulse_type,
                    'right_pulse_type'                  => $right_pulse_type,
                    'left_sensitivity_type'             => $left_sensitivity_type,
                    'right_sensitivity_type'            => $right_sensitivity_type,
                    'temperature_type'                  => $temperature_type,
                    'foot_color_type'                   => $foot_color_type,
                    'observations'                      => $observations,
                    'advice'                            => $advice,
                    'uid'                               => $uid,
                ]);
            } else {
                $this->consultationsRepository->updateConsultationPodiatricExploration([
                    'uuid'                              => $podiatric_exploration_uuid,
                    'foot_type'                         => $foot_type,
                    'metatarsal_formula'                => $metatarsal_formula,
                    'gait_disorder'                     => $gait_disorder,
                    'left_pulse_type'                   => $left_pulse_type,
                    'right_pulse_type'                  => $right_pulse_type,
                    'left_sensitivity_type'             => $left_sensitivity_type,
                    'right_sensitivity_type'            => $right_sensitivity_type,
                    'temperature_type'                  => $temperature_type,
                    'foot_color_type'                   => $foot_color_type,
                    'observations'                      => $observations,
                    'advice'                            => $advice,
                ]);
            }
            
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    function getConsultationProcedures($data): ?array {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $consultation_procedures_data = $this->consultationsRepository->getConsultationProcedures($consultation_uuid);
            $consultation_procedures = array();

            foreach($consultation_procedures_data as $d) {
                array_push($consultation_procedures, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'procedure_id'              => $this->uuidBinaryToString($d['servicio_uuid']),
                    'procedure_code'            => $d['codigo_servicio'],
                    'procedure'                 => $d['servicio'],
                    'quantity'                  => intval($d['cantidad']),
                    'unit_price'                => $d['precio_unitario'],
                    'bonus'                     => $d['bonificacion'],
                    'total'                     => $d['total'],
                    'chargeable'                => $d['cobrable'],
                    'origin'                    => $d['origen'],
                    'observations'              => $d['observaciones'],
                ));
            }

            return $consultation_procedures;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function getProcedureData($data): ?array {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            if($uid === -1)
                throw new RuntimeException('No existe una sesion activa.');

            $uuid = $this->normalizeRequiredText(
                $data['procedure'] ?? null,
                'Error al recibir identificador del procedimiento.'
            );


            $procedure_uuid = $this->uuidStringToBinary($uuid);
            $procedure_data = $this->consultationsRepository->getProcedureData([
                'uuid'                          => $procedure_uuid,
                'uid'                           => $uid,
            ]);

            return [
                'id'                                => $this->uuidBinaryToString($procedure_data['uuid']),
                'code'                              => $procedure_data['codigo'],
                'procedure'                         => $procedure_data['servicio'],
                'cost'                              => $procedure_data['costo'] ?? $procedure_data['costo_base'],
                'in_list'                           => $procedure_data['costo'] ? 1 : 0,
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function updateConsultationsProcedures($data): void {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $conn = $this->consultationsRepository->getConnection();
            $conn->beginTransaction();

            $procedures = json_decode($data['procedures']);

            // die(var_dump($procedures));

            foreach($procedures as $p) {
                if($p->action != 'no_changes') {
                    $procedure_uuid = $this->uuidStringToBinary($p->procedure_id);
                    $procedure_data = $this->consultationsRepository->getProcedureData([
                        'uuid'                          => $procedure_uuid,
                        'uid'                           => $uid,
                    ]);
                    $procedure_cost = $procedure_data['costo'] ?? $procedure_data['costo_base'];
                    if(floatval($p->unit_price) != floatval($procedure_cost)) {
                        throw new RuntimeException("El costo del procedimiento ".$p->procedure." no corresponde");
                    }

                    if($p->action === 'add') {
                        if($this->consultationsRepository->checkIfExistsConsultationProcedure([
                                'consultation_uuid'             => $consultation_uuid, 
                                'procedure_uuid'                => $procedure_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");
                        $consultation_procedure_uuid = $this->generateUuidBinary();
                        $consultation_procedure_id = $this->consultationsRepository->addConsultationProcedure([
                            'uuid'                              => $consultation_procedure_uuid,
                            'consultation_uuid'                 => $consultation_uuid,
                            'quantity'                          => $p->quantity,
                            'unit_price'                        => $p->unit_price,
                            'bonus'                             => $p->bonus,
                            'total'                             => $p->total,
                            'origin'                            => $p->origin,
                            'chargeable'                        => $p->chargeable,
                            'procedure_id'                      => $procedure_data['id'],
                            'uid'                               => $uid,
                        ]);

                        if(!$consultation_procedure_id)
                            throw new RuntimeException("Ocurrio un error al registrar procedimiento.");
                    } else if($p->action === 'modify') {
                        if(!$this->consultationsRepository->checkIfExistsConsultationProcedure([
                                'consultation_uuid'             => $consultation_uuid, 
                                'procedure_uuid'                => $procedure_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");
                        
                        $consultation_procedure_uuid = $this->uuidStringToBinary($p->id);
                        $this->consultationsRepository->updateConsultationProcedure([
                            'uuid'                              => $consultation_procedure_uuid,
                            'quantity'                          => $p->quantity,
                            'bonus'                             => $p->bonus,
                            'total'                             => $p->total,
                            'chargeable'                        => $p->chargeable,
                            'origin'                            => $p->origin,
                        ]);
                    } else if($p->action === 'remove') {
                        if(!$this->consultationsRepository->checkIfExistsConsultationProcedure([
                                'consultation_uuid'             => $consultation_uuid, 
                                'procedure_uuid'                => $procedure_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");

                        $consultation_procedure_uuid = $this->uuidStringToBinary($p->id);
                        $procedure_origin = $this->consultationsRepository->getConsultationProcedureOrigin($consultation_procedure_uuid);
                        if($procedure_origin == 'agendado')
                            throw new RuntimeException("No es posible remover un procedimiento agendado.");
                        $this->consultationsRepository->removeConsultationProcedure($consultation_procedure_uuid);
                    }
                }
            }

            $conn->commit();
        } catch(\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    function getConsultationDiagnostics($data): ?array {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $consultation_diagnostics_data = $this->consultationsRepository->getConsultationDiagnostics($consultation_uuid);
            $consultation_diagnostics = array();

            foreach($consultation_diagnostics_data as $d) {
                array_push($consultation_diagnostics, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'diagnostic_id'             => $this->uuidBinaryToString($d['diagnostico_uuid']),
                    'diagnostic_code'           => $d['diagnostico_codigo'],
                    'diagnostic'                => $d['diagnostico'],
                    'type_id'                   => intval($d['tipo_id']),
                    'type_code'                 => $d['tipo_codigo'],
                    'type'                      => $d['tipo'],
                    'observations'              => $d['observaciones'],
                ));
            }

            $diagnostic_summary = $this->consultationsRepository->getConsultationDiagnosticsSummary($consultation_uuid);

            return [
                'diagnostic_summary'                 => $diagnostic_summary,
                'consultation_diagnostics'           => $consultation_diagnostics,
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function getDiagnosticData($data): ?array {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            if($uid === -1)
                throw new RuntimeException('No existe una sesion activa.');

            $uuid = $this->normalizeRequiredText(
                $data['diagnostic'] ?? null,
                'Error al recibir identificador del diagnostico.'
            );


            $diagnostic_uuid = $this->uuidStringToBinary($uuid);
            $diagnostic_data = $this->consultationsRepository->getDiagnosticData([
                'uuid'                          => $diagnostic_uuid
            ]);

            return [
                'id'                                => $this->uuidBinaryToString($diagnostic_data['uuid']),
                'code'                              => $diagnostic_data['codigo'],
                'diagnostic'                        => $diagnostic_data['diagnostico'],
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function updateConsultationsDiagnostics($data): void {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $conn = $this->consultationsRepository->getConnection();
            $conn->beginTransaction();

            $diagnostics = json_decode($data['diagnostics']);

            $this->consultationsRepository->updateConsultationDiagnosticObservations([
                'uuid'                          => $consultation_uuid,
                'observations'                  => $data['observations'],
            ]);

            foreach($diagnostics as $d) {
                if($d->action != 'no_changes') {
                    $diagnostic_uuid = $this->uuidStringToBinary($d->diagnostic_id);
                    $diagnostic_id = $this->diagnosticsRepository->getDiagnosticId($diagnostic_uuid);
                    $diagnostic_name = $this->diagnosticsRepository->getDiagnosticName($diagnostic_uuid);
                    
                    if($d->action === 'add') {
                        if($this->consultationsRepository->checkIfExistsConsultationDiagnostic([
                                'consultation_uuid'             => $consultation_uuid, 
                                'diagnostic_uuid'               => $diagnostic_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");
                            
                        $consultation_diagnostic_uuid = $this->generateUuidBinary();
                        $consultation_diagnostic_id = $this->consultationsRepository->addConsultationDiagnostic([
                            'uuid'                              => $consultation_diagnostic_uuid,
                            'consultation_uuid'                 => $consultation_uuid,
                            'diagnostic_type'                   => $d->type_id,
                            'diagnostic'                        => $diagnostic_id,
                            'diagnostic_name'                   => $diagnostic_name,
                            'observations'                      => $d->observations,
                            'uid'                               => $uid,
                        ]);

                        if(!$consultation_diagnostic_id)
                            throw new RuntimeException("Ocurrio un error al registrar el diagnostico.");
                    } else if($d->action === 'modify') {
                        if(!$this->consultationsRepository->checkIfExistsConsultationDiagnostic([
                                'consultation_uuid'             => $consultation_uuid, 
                                'diagnostic_uuid'                => $diagnostic_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");
                        
                        $consultation_diagnostic_uuid = $this->uuidStringToBinary($d->id);
                        $this->consultationsRepository->updateConsultationDiagnostic([
                            'uuid'                              => $consultation_diagnostic_uuid,
                            'diagnostic_type'                   => $d->type_id,
                            'diagnostic_name'                   => $diagnostic_name,
                            'observations'                      => $d->observations,
                        ]);
                    } else if($d->action === 'remove') {
                        if(!$this->consultationsRepository->checkIfExistsConsultationDiagnostic([
                                'consultation_uuid'             => $consultation_uuid, 
                                'diagnostic_uuid'               => $diagnostic_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");

                        $consultation_diagnostic_uuid = $this->uuidStringToBinary($d->id);
                        $this->consultationsRepository->removeConsultationDiagnostic($consultation_diagnostic_uuid);
                    }
                }
            }

            $conn->commit();
        } catch(\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    function getConsultationSores($data): ?array {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $consultation_sores_data = $this->consultationsRepository->getConsultationSores($consultation_uuid);
            $consultation_sores = array();

            foreach($consultation_sores_data as $d) {
                array_push($consultation_sores, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'sore_id'                   => intval($d['tipo_lesion_id']),
                    'sore_code'                 => $d['tipo_lesion_codigo'],
                    'sore'                      => $d['tipo_lesion'],
                    'laterality_id'             => intval($d['lateralidad_id'] ?? 0),
                    'laterality'                => $d['lateralidad'] ?? '',
                    'location'                  => $d['ubicacion'],
                    'length'                    => $d['largo_cm'],
                    'width'                     => $d['ancho_cm'],
                    'depth'                     => $d['profundidad_cm'],
                    'wagner_scale_id'           => intval($d['grado_wagner_id'] ?? 0),
                    'wagner_scale'              => $d['grado_wagner'] ?? '',
                    'tissue_id'                 => intval($d['tipo_tejido_id'] ?? 0),
                    'tissue'                    => $d['tipo_tejido'] ?? '',
                    'evolution_id'              => intval($d['tipo_evolucion_id'] ?? 0),
                    'evolution'                 => $d['tipo_evolucion'] ?? '',
                    'exudate_id'                => intval($d['tipo_exudado_id'] ?? 0),
                    'exudate'                   => $d['tipo_exudado'] ?? '',
                    'exudate_color_id'          => intval($d['color_exudado_id'] ?? 0),
                    'exudate_color'             => $d['color_exudado'] ?? '',
                    'infection_signs'           => intval($d['signos_infeccion'] ?? 0),
                    'pain_scale'                => intval($d['dolor'] ?? 0),
                    'observations'              => $d['observaciones'] ?? '',
                ));
            }

            return $consultation_sores;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function updateConsultationsSores($data): void {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $conn = $this->consultationsRepository->getConnection();
            $conn->beginTransaction();

            $sores = json_decode($data['sores']);

            foreach($sores as $s) {
                if($s->action != 'no_changes') {
                    $sore = $this->normalizeRequiredInt(
                        $s->sore_type_id ?? -1,
                        'Es necesario seleccionar una lesión.'
                    );

                    $laterality = $this->normalizeRequiredInt(
                        $s->laterality_id ?? -1,
                        'Es necesario seleccionar el pie donde se encuentra la lesión.'
                    );

                    $location = $this->normalizeRequiredText(
                        $s->location ?? null,
                        'Agrega la ubicación de la lesión.'
                    );

                    if($sore === -1) 
                        throw new InvalidArgumentException('Es necesario seleccionar una lesión.');

                    if($laterality === -1) 
                        throw new InvalidArgumentException('Es necesario seleccionar el pie donde se encuentra la lesión.');

                    if($location === -1) 
                        throw new InvalidArgumentException('Agrega la ubicación de la lesión.');

                    $length = $this->normalizeOptionalInt($s->length ?? -1);
                    $width = $this->normalizeOptionalInt($s->width ?? -1);
                    $depth = $this->normalizeOptionalInt($s->depth ?? -1);

                    $wagner_scale = $this->normalizeOptionalInt($s->wagner_scale_id ?? 0);
                    $tissue = $this->normalizeOptionalInt($s->tissue_id ?? 0);
                    $evolution = $this->normalizeOptionalInt($s->evolution_id ?? 0);
                    $exudate = $this->normalizeOptionalInt($s->exudate_id ?? 0);
                    $exudate_color = $this->normalizeOptionalInt($s->exudate_color_id ?? 0);

                    $wagner_scale = $wagner_scale != 0 ? $wagner_scale : null;
                    $tissue = $tissue != 0 ? $tissue : null;
                    $evolution = $evolution != 0 ? $evolution : null;
                    $exudate = $exudate != 0 ? $exudate : null;
                    $exudate_color = $exudate_color != 0 ? $exudate_color : null;

                    $infection_signs = $this->normalizeOptionalInt($s->infection_signs ?? 0);
                    $pain_scale = $this->normalizeOptionalInt($s->pain_scale ?? 0);
                    $observations = $this->normalizeOptionalText(trim($s->observations) ?? '');

                    if($infection_signs == null)
                        $infection_signs = 0;

                    if($pain_scale == null)
                        $pain_scale = 0;

                    if($s->action === 'add') {
                        $consultation_diagnostic_uuid = $this->generateUuidBinary();
                        $consultation_diagnostic_id = $this->consultationsRepository->addConsultationSore([
                            'uuid'                              => $consultation_diagnostic_uuid,
                            'sore'                              => $sore,
                            'laterality'                        => $laterality,
                            'location'                          => $location,
                            'length'                            => $length,
                            'width'                             => $width,
                            'depth'                             => $depth,
                            'wagner_scale'                      => $wagner_scale,
                            'tissue'                            => $tissue,
                            'evolution'                         => $evolution,
                            'exudate'                           => $exudate,
                            'exudate_color'                     => $exudate_color,
                            'infection_signs'                   => $infection_signs,
                            'pain_scale'                        => $pain_scale,
                            'observations'                      => $s->observations,
                            'uid'                               => $uid,
                            'consultation_uuid'                 => $consultation_uuid,
                        ]);

                        if(!$consultation_diagnostic_id)
                            throw new RuntimeException("Ocurrio un error al registrar la lesión.");
                    } else if($s->action === 'modify') {
                        $sore_uuid = $this->uuidStringToBinary($s->id);
                        if(!$this->consultationsRepository->checkIfExistsConsultationSore([
                                'consultation_uuid'             => $consultation_uuid, 
                                'sore_uuid'                     => $sore_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");
                        
                        $consultation_sore_uuid = $this->uuidStringToBinary($s->id);
                        $this->consultationsRepository->updateConsultationSore([
                            'uuid'                              => $consultation_sore_uuid,
                            'location'                          => $s->location,
                            'observations'                      => $s->observations,
                        ]);
                    } else if($s->action === 'remove') {
                        $sore_uuid = $this->uuidStringToBinary($s->id);
                        if(!$this->consultationsRepository->checkIfExistsConsultationSore([
                                'consultation_uuid'             => $consultation_uuid, 
                                'sore_uuid'                     => $sore_uuid
                            ]))
                            throw new RuntimeException("Por favor revisa la lista de nuevo");

                        $consultation_sore_uuid = $this->uuidStringToBinary($s->id);
                        $this->consultationsRepository->removeConsultationSore($consultation_sore_uuid);
                    }
                }
            }

            $conn->commit();
        } catch(\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    function updateConsultationEvidenceUpload($data): ?array {
        $originalPath = '';
        $thumbPath = '';
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $type = $this->normalizeRequiredText(
                $data['type'] ?? null,
                'Error al recibir tipo de evidencia antes/despues.'
            );

            if($data['evidence'] === null)
                throw new RuntimeException("No se recibio archivo.");

            if ($data['evidence']['error'] !== UPLOAD_ERR_OK)
                throw new RuntimeException('Error al subir archivo');

            if ($data['evidence']['size'] > 5 * 1024 * 1024)
                throw new RuntimeException('La imagen supera 5MB');

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);
            $consultation_uuid_plain = $this->uuidBinaryToString($consultation_uuid);

            $tmpPath = $data['evidence']['tmp_name'];

            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($tmpPath);

            $allowed = [
                'image/jpeg',
                'image/png',
                'image/webp'
            ];

            if (!in_array($mime, $allowed, true))
                throw new RuntimeException('Formato no permitido');

            $imageInfo = getimagesize($tmpPath);

            if ($imageInfo === false)
                throw new RuntimeException('Archivo inválido');

            switch ($mime) {
                case 'image/jpeg':
                    $source = imagecreatefromjpeg($tmpPath);
                    break;

                case 'image/png':
                    $source = imagecreatefrompng($tmpPath);
                    break;

                case 'image/webp':
                    $source = imagecreatefromwebp($tmpPath);
                    break;

                default:
                    $source = false;
            }

            if (!$source)
                throw new RuntimeException('No se pudo procesar imagen');
            
            $evidence_uuid = $this->generateUuidBinary();
            $evidence_uuid_plain = $this->uuidBinaryToString($evidence_uuid);

            $relativePath = 'consultations/'.$consultation_uuid_plain.'/evidence/';
            $basePath = STORAGE_PATH . '/' . $relativePath;

            if (!is_dir($basePath)) {
                if (!mkdir($basePath, 0775, true) && !is_dir($basePath)) {
                    throw new RuntimeException('No fue posible crear el directorio de evidencia.');
                }
            }

            if (!is_writable($basePath)) {
                throw new RuntimeException('El directorio de evidencia no tiene permisos de escritura.');
            }

            $thumbsRelativePath = $relativePath . 'thumbs/';
            $thumbsPath = $basePath . 'thumbs/';

            if (!is_dir($thumbsPath)) {
                mkdir($thumbsPath, 0775, true);
            }

            $originalName = $evidence_uuid_plain . '.jpg';
            $thumbName = $evidence_uuid_plain . '_thumb.jpg';

            $originalPath = $basePath . $originalName;
            $originalRelativePath = $relativePath . $originalName;
            $thumbPath = $thumbsPath . $thumbName;
            $thumbRelativePath = $thumbsRelativePath . $thumbName;

            imagejpeg($source, $originalPath, 85);

            $width = imagesx($source);
            $height = imagesy($source);

            $thumbWidth = 300;
            $thumbHeight = 300;

            $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);

            $srcRatio = $width / $height;
            $thumbRatio = $thumbWidth / $thumbHeight;

            if ($srcRatio > $thumbRatio) {
                $newHeight = $height;
                $newWidth = (int)($height * $thumbRatio);
                $srcX = (int)(($width - $newWidth) / 2);
                $srcY = 0;
            } else {
                $newWidth = $width;
                $newHeight = (int)($width / $thumbRatio);
                $srcX = 0;
                $srcY = (int)(($height - $newHeight) / 2);
            }

            imagecopyresampled(
                $thumb,
                $source,
                0,
                0,
                $srcX,
                $srcY,
                $thumbWidth,
                $thumbHeight,
                $newWidth,
                $newHeight
            );

            imagejpeg($thumb, $thumbPath, 80);

            $extension = 'jpg';
            $mime = 'image/jpeg';
            $size = filesize($originalPath);
            $hash = hash_file('sha256', $originalPath);
            $thumbSize = filesize($thumbPath);
            $thumbHash = hash_file('sha256', $thumbPath);

            imagedestroy($source);
            imagedestroy($thumb);
            
            $inserted_id = $this->consultationsRepository->insertConsultationEvidence([
                'uuid'                              => $evidence_uuid,
                'type'                              => $type,
                'extension'                         => $extension,
                'width'                             => $width,
                'height'                            => $height,
                'size'                              => $size,
                'thumb_size'                        => $thumbSize,
                'hash'                              => $hash,
                'thumb_hash'                        => $thumbHash,
                'base_path'                         => $relativePath,
                'file_name'                         => $originalName,
                'thumb_base_path'                   => $thumbRelativePath,
                'thumb_name'                        => $thumbName,
                'uid'                               => $uid,
                'consultation_uuid'                 => $consultation_uuid,
            ]);
            
            if(!$inserted_id) {
                unlink($originalPath);
                unlink($thumbPath);
                throw new RuntimeException("Ocurrio un error al intentar almacenar el registro.");
            }

            return [
                'block_id'                          => $uuid,
                'consultation_id'                   => $consultation_uuid_plain,
                'evidence_id'                       => $evidence_uuid_plain,
                'image_url'                         => $originalPath,
                'thumb_url'                         => $thumbPath
            ];
        } catch(\Throwable $e) {
            if($originalPath != '' && file_exists($originalPath))
                unlink($originalPath);
            if($thumbPath != '' && file_exists($thumbPath))
                unlink($thumbPath);
            throw $e;
        }
    }

    function updateConsultationIndications($data): void {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $indications = $this->normalizeRequiredText(
                $data['indications'] ?? "",
                'Error al recibir indicaciones de la consulta.'
            );

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);

            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $this->consultationsRepository->updateConsultationIndications([
                'uuid'                          => $consultation_uuid,
                'indications'                   => $data['indications'],
            ]);
        } catch(\Throwable $e) {
            throw $e;
        }
    }

    function finishConsultation($data): void {
        try {
            $uid = $this->normalizeRequiredInt(
                $data['uid'] ?? -1,
                'No existe una sesion activa.'
            );

            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador de la consulta.'
            );

            $conn = $this->consultationsRepository->getConnection();
            $conn->beginTransaction();

            $appointment_block_uuid = $this->uuidStringToBinary($data['uuid']);
            $appointment_block_id = $this->appointmentsRepository->getAppointmentBlockId($appointment_block_uuid);
            $appointment_uuid = $this->consultationsRepository->getAppointmentUuidByBlock($appointment_block_uuid);
            $consultation_uuid = $this->consultationsRepository->getConsultationUuidByBlock($appointment_block_uuid);

            $validate_data = $this->validBeforeFinish($consultation_uuid);
            $total = $validate_data['total'];
            $discount = $validate_data['discount'];
            $debt = $validate_data['debt'];
            $procedures = $validate_data['procedures'];
            $next_appointment = $validate_data['next_appointment'];

            $processStatus = $this->normalizeRequiredInt(
                $this->appointmentsStatusRepository->getIdByCode('en_proceso') ?? null,
                'Ocurrio un error al intentar obtener información.'
            );

            $blockProcessStatus = $this->normalizeRequiredInt(
                $this->appointmentsStatusRepository->getBlockIdByCode('en_proceso') ?? null,
                'Ocurrio un error al intentar obtener información.'
            );

            $finishStatus = $this->normalizeRequiredInt(
                $this->appointmentsStatusRepository->getIdByCode('finalizada') ?? null,
                'Ocurrio un error al intentar obtener información.'
            );

            $blockFinishStatus = $this->normalizeRequiredInt(
                $this->appointmentsStatusRepository->getBlockIdByCode('finalizada') ?? null,
                'Ocurrio un error al intentar obtener información.'
            );

            $this->consultationsRepository->finishConsultation([
                'uuid'                                  => $consultation_uuid,
                'uid'                                   => $uid,
            ]);

            $procedures_data = $this->consultationsRepository->getAppointmentProcedures($appointment_uuid, $uid);
            $time_end = 0;
            foreach($procedures_data as $pd) {
                if($time_end == 0)
                    $time_end = $pd['h_fin'];
                else if($pd['h_inicio' != $time_end])
                    break;
                $this->appointmentsRepository->finishAppointmentBlock([
                    'block'                             => $pd['block_uuid'],
                    'status'                            => $blockFinishStatus,
                    'uid'                               => $uid,
                ]);
            }

            $unfinished_blocks = $this->appointmentsRepository->getUnfinishedAppointmentBlocksCount([
                'uuid'                                  => $appointment_uuid,
                'status'                                => $blockFinishStatus,
            ]);
            if($unfinished_blocks['pendientes'] == 0) {
                $this->appointmentsRepository->finishAppointment([
                    'uuid'                              => $appointment_uuid,
                    'status'                            => $finishStatus,
                    'uid'                               => $uid,
                ]);
            }

            $sale_pending_status = $this->salesStatusRepository->getIdByCode('pendiente');

            $year = date('Y');
            $c_aux = $this->foliosRepository->getConsecutive('venta', $year);
            $sale_consecutive = $c_aux + 1;
            $folio = 'V-' . str_pad((string) $sale_consecutive, 7, '0', STR_PAD_LEFT) . '/' . substr($year, -2);

            $sale_uuid = $this->generateUuidBinary();

            $sale_id = $this->salesRepository->createFromConsultation([
                'uuid'                                      => $sale_uuid,
                'folio'                                     => $folio,
                'consecutive'                               => $sale_consecutive,
                'subtotal'                                  => $total,
                'total'                                     => $total,
                'discount'                                  => $discount,
                'debt'                                      => $debt,
                'status'                                    => $sale_pending_status,
                'observations'                              => '',
                'uid'                                       => $uid,
                'consultation_uuid'                         => $consultation_uuid,
            ]);

            foreach($procedures as $p) {
                $sale_detail_uuid = $this->generateUuidBinary();
                $this->salesRepository->createFromConsultationDetails([
                    'uuid'                                  => $sale_detail_uuid,
                    'sale'                                  => $sale_id,
                    'procedure'                             => $p['id'],
                    'description'                           => $p['service'],
                    'quantity'                              => $p['quantity'],
                    'unit_price'                            => $p['unit_price'],
                    'subtotal'                              => $p['total'],
                    'discount'                              => $p['discount'],
                    'total'                                 => $p['total'],
                    'debt'                                  => $p['debt'],
                ]);
            }

            $this->foliosRepository->updateConsecutive('venta', $year, $sale_consecutive);

            $conn->commit();
        } catch(\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    function validBeforeFinish($consultation_uuid): ?array {
        $consultation_data = $this->consultationsRepository->getConsultationStatus($consultation_uuid);
        if($consultation_data != null && intval($consultation_data['estatus']) == 0) {
            $next_appointment = null;

            $modules = $this->proceduresRepository->getConsultationProcedureModules($consultation_uuid);
            foreach($modules as $m) {
                if(intval($m['obligatorio']) == 1) {
                    switch($m['codigo']) {
                        case 'exploracion-podologica':
                            if(!$this->consultationsRepository->checkIfConsultationPodriaticExplorationExists($consultation_uuid))
                                throw new RuntimeException('No existe exploración podologica registrada.');
                            break;
                        case 'diagnosticos':
                            $consultation_diagnostics = $this->consultationsRepository->getConsultationDiagnostics($consultation_uuid);
                            if(count($consultation_diagnostics) == 0)
                                throw new RuntimeException("No hay diagnosticos registrados.");
                            break;
                        case 'lesiones_ulceras':
                            $consultation_sores = $this->consultationsRepository->getConsultationSores($consultation_uuid);
                            if(count($consultation_sores) == 0)
                                throw new RuntimeException("No hay lesiones registradas.");
                            break;
                        case 'plantillas':
                            break;
                        case 'evidencia-fotografica':
                            $consultation_evidence = $this->consultationsRepository->getConsultationEvidence($consultation_uuid);
                            if(count($consultation_evidence) == 0)
                                throw new RuntimeException("No hay evidencia registrada");
                            break;
                    }
                }
            }

            $consultation_procedures = $this->consultationsRepository->getConsultationProcedures($consultation_uuid);
            $total = 0;
            $discount = 0;
            $procedures = array();
            foreach($consultation_procedures as $cp) {
                if(intval($cp['cobrable']) == 1) {
                    $chargeable = 1;
                    $procedure_total = $cp['cantidad'] * $cp['precio_unitario'];
                    $debt = $procedure_total - $cp['bonificacion'];
                    $total += $debt;
                    $discount += $cp['bonificacion'];
                    array_push($procedures, array(
                        'id'                                => $cp['id'],
                        'uuid'                              => $cp['uuid'],
                        'service_id'                        => $cp['servicio_id'],
                        'service_uuid'                      => $cp['servicio_uuid'],
                        'service'                           => $cp['servicio'],
                        'quantity'                          => $cp['cantidad'],
                        'unit_price'                        => $cp['precio_unitario'],
                        'discount'                          => $cp['bonificacion'],
                        'total'                             => $procedure_total,
                        'debt'                              => $debt
                    ));
                }
            }
            if(count($procedures) == 0)
                throw new RuntimeException("Debe existir al menos un procedimiento cobrable.");

            if($total < 0)
                throw new RuntimeException("El total de la consulta es inválido.");

            return [
                'total'                     => $total,
                'discount'                  => $discount,
                'debt'                      => $total - $discount,
                'procedures'                => $procedures,
                'next_appointment'          => $next_appointment,
            ];

        } else {
            throw new RuntimeException("La consulta no esta abierta para hacer cambios.");
        }
    }
}