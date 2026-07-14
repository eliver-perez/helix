<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\AppointmentsRepository;
use App\Repositories\AppointmentsTypesRepository;
use App\Repositories\BookingChannelsRepository;
use App\Repositories\AppointmentsStatusRepository;
use App\Repositories\PatientsRepository;
use App\Repositories\StaffRepository;
use App\Repositories\ProceduresRepository;
use App\Repositories\SettingsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class AppointmentsService extends Service
{
    public function __construct(
        private AppointmentsRepository $appointmentsRepository,
        private AppointmentsTypesRepository $appointmentsTypesRepository,
        private BookingChannelsRepository $bookingChannelRepository,
        private AppointmentsStatusRepository $appointmentsStatusRepository,
        private PatientsRepository $patientsRepository,
        private StaffRepository $staffRepository,
        private ProceduresRepository $proceduresRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAvailableSlots(string $date, array $procedures): array {
        $settingsService = new SettingsService($this->settingsRepository);
        $interval = $settingsService->get('agenda_intervalo_minutos', 'P');
    }

    public function scheduleAppointment(array $data): string {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');

        $date = $this->formatDateToSQL($data['appointment']['date'] ?? null);
        $appointmentStart = $this->timeToMinutes($data['appointment']['start']);
        $appointmentEnd = $this->timeToMinutes($data['appointment']['end']);
        $duration = $data['appointment']['duration'];
        $procedures = $data['appointment']['procedures'];

        $status = $this->appointmentsStatusRepository->getIdByCode('agendada');
        $block_status = $this->appointmentsStatusRepository->getBlockIdByCode('agendada');

        if($duration != ($appointmentEnd - $appointmentStart)) {
            throw new InvalidArgumentException('Hay un problema con la duracion de la cita');
        }

        if(!is_array($procedures) || count($procedures) === 0) {
            throw new InvalidArgumentException('No se recibio la lista de procedimientos de la cita');
        }

        $cost = 0;
        $procedures_cost = array();
        foreach($procedures as $p) {
            $procedures_cost[$p['procedure_id'].':'.$p['staff_id']] = $this->appointmentsRepository->getProcedureCost($this->uuidStringToBinary($p['procedure_id']), $this->uuidStringToBinary($p['staff_id']));
            $cost += $procedures_cost[$p['procedure_id'].':'.$p['staff_id']];
        }

        $patientId = $this->normalizeRequiredText(
            $data['patient'] ?? null,
            'La seleccion del paciente es obligatorio.'
        );

        $patientIdInt = $this->patientsRepository->getPatientId($this->uuidStringToBinary($patientId));

        $appointmentType = $this->normalizeRequiredInt(
            $data['appointment_type'] ?? null,
            'El tipo de cita es obligatorio.'
        );

        $bookingChannel = $this->normalizeRequiredInt(
            $data['booking_channel'] ?? null,
            'La opcion de como se agendo la cita es obligatorio.'
        );

        $chiefComplaint = $this->normalizeOptionalText($data['chief_complaint'] ?? null);

        if (!$this->appointmentsTypesRepository->existsById($appointmentType)) {
            throw new InvalidArgumentException('El tipo de cita no existe.');
        }

        if (!$this->bookingChannelRepository->existsById($bookingChannel)) {
            throw new InvalidArgumentException('El metodo como se agendo la cita no existe.');
        }

        $conn = $this->appointmentsRepository->getConnection();
        $conn->beginTransaction();
        try {
            $appointmentUuid = $this->generateUuidBinary();
            $appointmentId = $this->appointmentsRepository->insertAppointment([
                    'uuid'                          => $appointmentUuid,
                    'patient'                       => $patientIdInt,
                    'appointment_type'              => $appointmentType,
                    'booking_channel'               => $bookingChannel,
                    'chief_complaint'               => $chiefComplaint,
                    'appointment_date'              => $date,
                    'appointment_start'             => $appointmentStart,
                    'appointment_duration'          => $duration,
                    'appointment_end'               => $appointmentEnd,
                    'uid'                           => $uid,
                    'appointment_cost'              => $cost,
                    'status'                        => $status
                ]);

            $order = 1;
            foreach($procedures as $p) {
                $block_start = $this->timeToMinutes($p['start']);
                $block_end = $this->timeToMinutes($p['end']);
                $block_duration = $block_end - $block_start;

                $staffIdInt = $this->staffRepository->getStaffId($this->uuidStringToBinary($p['staff_id']));
                $procedureIdInt = $this->proceduresRepository->getProcedureId($this->uuidStringToBinary($p['procedure_id']));

                $appointmentBlockUuid = $this->generateUuidBinary();
                $this->appointmentsRepository->insertAppointmentBlock([
                    'uuid'                          => $appointmentBlockUuid,
                    'appointment'                   => $appointmentId,
                    'staff'                         => $staffIdInt,
                    'procedure'                     => $procedureIdInt,
                    'order'                         => $order++,
                    'start'                         => $block_start,
                    'end'                           => $block_end,
                    'duration'                      => $block_duration,
                    'status'                        => $block_status
                ]);

                $this->appointmentsRepository->insertAppointmentProcedure([
                    'appointment'                   => $appointmentId,
                    'procedure'                     => $procedureIdInt,
                    'staff'                         => $staffIdInt,
                    'cost'                          => $procedures_cost[$p['procedure_id'].':'.$p['staff_id']]
                ]);
            }

            $conn->commit();
            return $this->uuidBinaryToString($appointmentUuid);
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    public function getCalendarAppointments($start, $end): array {
        try {
            $startDate = new \DateTimeImmutable($start);
            $endDate   = new \DateTimeImmutable($end);
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Formato de fechas inválido');
        }

        try {
            $start_date = $startDate->format('Y-m-d');
            $end_date   = $endDate->format('Y-m-d');

            $data = $this->appointmentsRepository->getCalendarAppointments($start_date, $end_date);
            $appointments = array();

            foreach($data as $d) {
                array_push($appointments, array(
                    'id' => $this->uuidBinaryToString($d['uuid']),
                    'title' => $d['paciente'],
                    'start' => $d['fecha'].'T'.$this->minutesToTime($d['h_inicio']).':00',
                    'end' => $d['fecha'].'T'.$this->minutesToTime($d['h_fin']).':00',
                    'className' => $d['classname'] ?? 'primary',
                    'backgroundColor' => $d['background'] ?? '#EAEAEA',
                    'borderColor' => $d['text_color'] ?? '#000000',
                    'textColor' => $d['text_color'] ?? '#FFFFFF',
                    'extendedProps' => [
                        'patient' => $d['paciente'],
                        'patient_code' => $d['clave_paciente'] ?? '',
                        'status' => $d['estatus_codigo'],
                        'appointment_type' => $d['asunto'],
                        'description' => $d['motivo_consulta'] ?? '',
                        'dob' => $d['f_nacimiento'] ?? '',
                        'age' => $d['f_nacimiento'] ? $this->calculateAge($d['f_nacimiento']) : '',
                        'email' => $d['email'] ?? '',
                        'phone' => $d['telefono'] ?? '',
                        'gender' => $d['genero'] ?? '',
                    ],
                ));
            }

            return $appointments;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function calculateAppointmentAvailability($date, $procedures): array {
        $slots = [];

        $settingsService = new SettingsService($this->settingsRepository);
        $interval = (int)$settingsService->get('agenda_intervalo_minutos');
        
        $date_sql = \DateTime::createFromFormat('d/m/Y', $date);

        if ($interval <= 0) {
            $interval = 15;
        }

        $staffAvailability = [];
        $staffNames = [];
        $procedureCosts = [];

        foreach ($procedures as $procedure) {
            // $staffId = (int)($procedure['staffId'] ?? 0);
            // $procedureId = (int)($procedure['procedureId'] ?? 0);
            $staffId = $this->uuidStringToBinary($procedure['staffId']);
            $procedureId = $this->uuidStringToBinary($procedure['procedureId']);

            if ($staffId <= 0 || $procedureId <= 0) {
                throw new InvalidArgumentException('Procedimiento inválido');
            }

            if (isset($staffAvailability[$staffId])) {
                continue;
            }

            $staffNames[$staffId] = $this->appointmentsRepository->getStaffName($staffId);
            $procedureName = $this->appointmentsRepository->getProcedureName($procedureId);

            $procedureCosts[$procedureId] = $this->appointmentsRepository->getProcedureCost(
                $procedureId,
                $staffId
            );

            $intervals = $this->appointmentsRepository->getStaffAvailability(
                $staffId,
                $date_sql->format('Y-m-d'),
                $interval
            );

            if (count($intervals) === 0) {
                return [];
            }

            $staffAvailability[$staffId] = $intervals;
        }

        $firstProcedure = $procedures[0];
        // $firstStaffId = (int)$firstProcedure['staffId'];
        $firstStaffId = $this->uuidStringToBinary($firstProcedure['staffId']);
        $firstDuration = (int)$firstProcedure['duration'];

        $firstIntervals = $staffAvailability[$firstStaffId];

        foreach ($firstIntervals as $baseInterval) {
            $baseStart = (int)$baseInterval['start'];
            $baseEnd = (int)$baseInterval['end'];

            for ($start = $baseStart; $start + $firstDuration <= $baseEnd; $start += $interval) {
                $currentStart = $start;
                $valid = true;
                $procedureBlocks = [];
                $totalDuration = 0;

                foreach ($procedures as $procedure) {
                    $procedureId = $this->uuidStringToBinary($procedure['procedureId']); 
                    $staffId = $this->uuidStringToBinary($procedure['staffId']);
                    $duration = (int)$procedure['duration'];

                    $currentEnd = $currentStart + $duration;

                    $intervals = $staffAvailability[$staffId] ?? [];

                    if (!$this->fitsInIntervals($intervals, $currentStart, $currentEnd)) {
                        $valid = false;
                        break;
                    }

                    $procedureBlocks[] = [
                        'procedure_id' => $this->uuidBinaryToString($procedureId),
                        'procedure' => $procedureName,
                        'staff_id' => $this->uuidBinaryToString($staffId),
                        'staff_name' => $staffNames[$staffId] ?? '',
                        'start' => $this->minutesToTime($currentStart),
                        'end' => $this->minutesToTime($currentEnd),
                        'cost' => $procedureCosts[$procedureId] ?? 0,
                    ];

                    $totalDuration += $duration;
                    $currentStart = $currentEnd;
                }

                if ($valid) {
                    $slots[] = [
                        'start' => $this->minutesToTime($start),
                        'end' => $this->minutesToTime($start + $totalDuration),
                        'duration' => $totalDuration,
                        'procedures' => $procedureBlocks,
                    ];
                }
            }
        }

        return $slots;
    }

    private function fitsInIntervals(array $intervals, int $start, int $end): bool
    {
        foreach ($intervals as $interval) {
            if ($start >= (int)$interval['start'] && $end <= (int)$interval['end']) {
                return true;
            }
        }

        return false;
    }

    public function checkIn(array $data) {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');

        $appointment = $this->normalizeRequiredText(
            $data['appointment'] ?? null,
            'No se recibio informacion de la cita.'
        );

        $appointmentId = $this->uuidStringToBinary($appointment);

        if (!$this->appointmentsRepository->appointmentExistsByUuid($appointmentId)) {
            throw new InvalidArgumentException('No se encontro informacion de la cita');
        }

        $blockScheduledStatus = $this->normalizeRequiredInt(
            $this->appointmentsStatusRepository->getBlockIdByCode('agendada') ?? null,
            'Ocurrio un error al intentar obtener información.'
        );

        $waitingStatus = $this->normalizeRequiredInt(
            $this->appointmentsStatusRepository->getIdByCode('en_espera') ?? null,
            'Ocurrio un error al intentar obtener información.'
        );

        $blockWaitingStatus = $this->normalizeRequiredInt(
            $this->appointmentsStatusRepository->getBlockIdByCode('en_espera') ?? null,
            'Ocurrio un error al intentar obtener información.'
        );

        $conn = $this->appointmentsRepository->getConnection();
        $conn->beginTransaction();
        try {
            $actual_status = $this->appointmentsRepository->appointmentStatus($appointmentId);
            
            if($actual_status != 'agendada')
                throw new RuntimeException('La cita no esta en un estatus valido para hacer check-in.');

            $appointmentBlockId = $this->appointmentsRepository->getFirstAppointmentBlock([
                'appointment'                       => $appointmentId,
                'status'                            => $blockScheduledStatus,
            ]);

            $this->appointmentsRepository->changeAppointmentStatus([
                'appointment'                       => $appointmentId,
                'status'                            => $waitingStatus,
            ]);

            $this->appointmentsRepository->changeAppointmentBlockStatus([
                'block'                             => $appointmentBlockId,
                'status'                            => $blockWaitingStatus,
            ]);

            $conn->commit();
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    public function cancel(array $data) {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');

        $appointment = $this->normalizeRequiredText(
            $data['appointment'] ?? null,
            'No se recibio informacion de la cita.'
        );

        $appointmentId = $this->uuidStringToBinary($appointment);

        if (!$this->appointmentsRepository->appointmentExistsByUuid($appointmentId)) {
            throw new InvalidArgumentException('No se encontro informacion de la cita');
        }

        $status = $this->normalizeRequiredInt(
            $this->appointmentsStatusRepository->getIdByCode('cancelada') ?? null,
            'Ocurrio un error al intentar obtener información.'
        );

        $refusedStatus = $this->normalizeRequiredInt(
            $this->appointmentsStatusRepository->getIdByCode('rechazada') ?? null,
            'Ocurrio un error al intentar obtener información.'
        );

        $attendedStatus = $this->normalizeRequiredInt(
            $this->appointmentsStatusRepository->getIdByCode('finalizada') ?? null,
            'Ocurrio un error al intentar obtener información.'
        );

        $noAssistanceStatus = $this->normalizeRequiredInt(
            $this->appointmentsStatusRepository->getIdByCode('no_presento') ?? null,
            'Ocurrio un error al intentar obtener información.'
        );

        $conn = $this->appointmentsRepository->getConnection();
        $conn->beginTransaction();
        try {
            $actual_status = $this->appointmentsRepository->appointmentStatus($appointmentId);
            
            if($actual_status == 'cancelada' || 
                $actual_status == 'rechazada' || 
                $actual_status == 'no_presento' || 
                $actual_status == 'finalizada')
                throw new RuntimeException('La cita no esta en un estatus válido para cancelarse.');

            $this->appointmentsRepository->changeAppointmentStatus([
                    'appointment'                   => $appointmentId,
                    'status'                        => $status,
                ]);

            $this->appointmentsRepository->cancelAppointmentBlocks([
                'appointment'                       => $appointmentId,
                'status'                            => [
                    'canceled'                      => $status,
                    'refused'                       => $refusedStatus,
                    'noAssistance'                  => $noAssistanceStatus,
                    'attended'                      => $attendedStatus,
                ],
            ]);

            $conn->commit();
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }
}