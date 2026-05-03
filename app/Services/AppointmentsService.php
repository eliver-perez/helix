<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\AppointmentsRepository;
use App\Repositories\AppointmentsTypesRepository;
use App\Repositories\BookingChannelsRepository;
use App\Repositories\AppointmentsStatusRepository;
use App\Repositories\SettingsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;

class AppointmentsService extends Service
{
    public function __construct(
        private AppointmentsRepository $appointmentsRepository,
        private AppointmentsTypesRepository $appointmentsTypesRepository,
        private BookingChannelsRepository $bookingChannelRepository,
        private AppointmentsStatusRepository $appointmentsStatusRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAvailableSlots(string $date, array $procedures): array {
        $settingsService = new SettingsService($this->settingsRepository);
        $interval = $settingsService->get('agenda_intervalo_minutos', 'P');
    }

    public function scheduleAppointment(array $data): int {
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
            $procedures_cost[$p['procedure_id'].':'.$p['staff_id']] = $this->appointmentsRepository->getProcedureCost($p['procedure_id'], $p['staff_id']);
            $cost += $procedures_cost[$p['procedure_id'].':'.$p['staff_id']];
        }

        $patientId = $this->normalizeRequiredInt(
            $data['patient'] ?? null,
            'La seleccion del paciente es obligatorio.'
        );

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
                    'patient'                       => $patientId,
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

                $appointmentBlockUuid = $this->generateUuidBinary();
                $this->appointmentsRepository->insertAppointmentBlock([
                    'uuid'                          => $appointmentBlockUuid,
                    'appointment'                   => $appointmentId,
                    'staff'                         => $p['staff_id'],
                    'procedure'                     => $p['procedure_id'],
                    'order'                         => $order++,
                    'start'                         => $block_start,
                    'end'                           => $block_end,
                    'duration'                      => $block_duration,
                    'status'                        => $block_status
                ]);

                $this->appointmentsRepository->insertAppointmentProcedure([
                    'appointment'                   => $appointmentId,
                    'procedure'                     => $p['procedure_id'],
                    'staff'                         => $p['staff_id'],
                    'cost'                          => $procedures_cost[$p['procedure_id'].':'.$p['staff_id']]
                ]);
            }

            $conn->commit();
            return $appointmentId;
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
                    'title' => $d['paciente'] . ' - ' . $d['asunto'],
                    'start' => $d['fecha'].'T'.$this->minutesToTime($d['h_inicio']).':00',
                    'end' => $d['fecha'].'T'.$this->minutesToTime($d['h_fin']).':00',
                    'className' => $d['classname'] ?? 'primary',
                    'backgroundColor' => $d['background'] ?? '#EAEAEA',
                    'borderColor' => $d['text_color'] ?? '#000000',
                    'textColor' => $d['text_color'] ?? '#FFFFFF',
                    'extendedProps' => [
                        'patient' => $d['paciente'],
                        'patient_code' => $d['clave_paciente'] ?? '',
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
            $staffId = (int)($procedure['staffId'] ?? 0);
            $procedureId = (int)($procedure['procedureId'] ?? 0);

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
        $firstStaffId = (int)$firstProcedure['staffId'];
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
                    $procedureId = (int)$procedure['procedureId'];
                    $staffId = (int)$procedure['staffId'];
                    $duration = (int)$procedure['duration'];

                    $currentEnd = $currentStart + $duration;

                    $intervals = $staffAvailability[$staffId] ?? [];

                    if (!$this->fitsInIntervals($intervals, $currentStart, $currentEnd)) {
                        $valid = false;
                        break;
                    }

                    $procedureBlocks[] = [
                        'procedure_id' => $procedureId,
                        'procedure' => $procedureName,
                        'staff_id' => $staffId,
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

    private function minutesToTime(int $minutes): string {
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;

        return sprintf('%02d:%02d', $hours, $mins);
    }

    function timeToMinutes(string $time): int {
        if (!preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', $time, $matches)) {
            throw new InvalidArgumentException("Formato de hora inválido: {$time}");
        }

        $h = (int) $matches[1];
        $m = (int) $matches[2];

        if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
            throw new InvalidArgumentException("Hora fuera de rango: {$time}");
        }

        return ($h * 60) + $m;
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
}