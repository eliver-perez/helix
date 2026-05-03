<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class AppointmentsRepository
{
    public function __construct(private PDO $db) {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getCalendarAppointments(string $start, string $end): array {
        $stmt = $this->db->prepare("
            SELECT
            c.uuid,
            TRIM(
                CONCAT(
                    p.nombre, ' ',
                    COALESCE(p.paterno, ''), ' ',
                    COALESCE(p.materno, '')
                )
            ) paciente,
            p.clave clave_paciente,
            COALESCE(DATE_FORMAT(p.f_nacimiento, '%d/%m/%Y'), '') f_nacimiento,
            p.email,
            p.telefono,
            g.genero,
            ca.asunto,
            c.fecha,
            c.motivo_consulta,
            c.h_inicio,
            c.h_fin,
            ce.text_color,
            ce.classname,
            ce.background
            FROM citas c
                INNER JOIN citas_estatus ce
                    ON c.estatus = ce.id
                INNER JOIN citas_asuntos ca
                    ON c.asunto = ca.id
                INNER JOIN pacientes p
                    ON c.paciente = p.id
                INNER JOIN generos g
                    ON p.genero = g.id
            WHERE c.fecha >= :start
                AND c.fecha < :end
            ");
        
        $stmt->execute([
            'start' => $start,
            'end' => $end
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProcedureCost(int $procedureId, int $staffId): float {
        $stmt = $this->db->prepare("
            SELECT ps.costo
            FROM servicios s
            INNER JOIN personal_servicios ps
                ON s.id = ps.servicio
            WHERE s.id = :procedure_id
                AND ps.personal = :staff_id
        ");

        $stmt->execute([
            'procedure_id' => $procedureId,
            'staff_id' => $staffId
        ]);

        $cost = $stmt->fetchColumn();

        if ($cost === false) {
            throw new \RuntimeException('Costo no encontrado');
        }

        return (float)$cost;
    }

    public function getStaffName(int $staffId): string {
        $stmt = $this->db->prepare("
            SELECT TRIM(
                CONCAT(
                    nombre, ' ',
                    COALESCE(paterno, ''), ' ',
                    COALESCE(materno, '')
                )
            )
            FROM personal
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $staffId
        ]);

        $name = $stmt->fetchColumn();

        if ($name === false) {
            throw new \RuntimeException("Staff no encontrado");
        }

        return $name;
    }

    public function getProcedureName(int $procedureId): string {
        $stmt = $this->db->prepare("
            SELECT servicio
            FROM servicios
            WHERE id = :id
        ");

        $stmt->execute([
            'id' => $procedureId
        ]);

        $name = $stmt->fetchColumn();

        if ($name === false) {
            throw new \RuntimeException("Procedimiento no encontrado");
        }

        return $name;
    }

    public function getStaffAvailability(int $staffId, string $date, int $interval): array {
        // 1. Día de la semana
        $weekday = (int)date('w', strtotime($date));

        /*
            DISPONIBILIDAD BASE
        */
        $stmt = $this->db->prepare("
            SELECT hld.hora_inicio AS start, hld.hora_fin AS end
            FROM horarios_laborales_detalles hld
            JOIN horarios_laborales h
                ON h.id = hld.horario
            WHERE h.personal = :staff
                AND hld.dia_semana = :day
                AND h.activo = 1
        ");

        $stmt->execute([
            'staff' => $staffId,
            'day' => $weekday
        ]);

        $dayAvailability = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /*
            BLOQUEOS
        */
        $stmt = $this->db->prepare("
            SELECT h_inicio AS start, h_fin AS end
            FROM bloqueos_agenda
            WHERE personal = :staff
                AND f_inicio <= :date_start
                AND f_fin >= :date_end
        ");

        $stmt->execute([
            'staff' => $staffId,
            'date_start' => $date,
            'date_end' => $date
        ]);

        $dayUnavailable = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /*
            CITAS ACTIVAS
        */
        $stmt = $this->db->prepare("
            SELECT cb.h_inicio AS start, cb.h_fin AS end
            FROM citas c
            JOIN citas_bloques cb ON c.id = cb.cita
            JOIN citas_estatus ce ON c.estatus = ce.id
            WHERE cb.personal = :staff
                AND ce.codigo NOT IN ('cancelada', 'rechazada')
                AND c.fecha = :date
        ");

        $stmt->execute([
            'staff' => $staffId,
            'date' => $date
        ]);

        $dayBusy = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /*
            NORMALIZACIÓN
        */
        $free = $this->normalize($dayAvailability);
        $unavailable = $this->clipToWindows($dayUnavailable, $free);
        $busy = $this->clipToWindows($dayBusy, $free);

        $free = $this->subtractIntervals($free, $unavailable);
        $free = $this->subtractIntervals($free, $busy);

        /*
            RECORTE SI ES HOY
        */
        if ($date === date('Y-m-d')) {
            $nowMinutes = (int)date('H') * 60 + (int)date('i');

            $remainder = $nowMinutes % $interval;
            if ($remainder !== 0) {
                $nowMinutes += ($interval - $remainder);
            }

            $free = $this->trimPastIntervals($free, $nowMinutes);
        }

        return $free;
    }

    private function normalize(array $intervals): array {
        $valid = [];

        foreach ($intervals as $iv) {
            if ((int)$iv['end'] > (int)$iv['start']) {
                $valid[] = [
                    'start' => (int)$iv['start'],
                    'end' => (int)$iv['end']
                ];
            }
        }

        if (empty($valid)) {
            return [];
        }

        usort($valid, fn($a, $b) =>
            $a['start'] === $b['start']
                ? $a['end'] <=> $b['end']
                : $a['start'] <=> $b['start']
        );

        $result = [$valid[0]];

        foreach ($valid as $current) {
            $last = &$result[count($result) - 1];

            if ($current['start'] <= $last['end']) {
                $last['end'] = max($last['end'], $current['end']);
            } else {
                $result[] = $current;
            }
        }

        return $result;
    }

    private function clipToWindows(array $intervals, array $windows): array {
        $result = [];

        foreach ($intervals as $iv) {
            foreach ($windows as $w) {
                $start = max($iv['start'], $w['start']);
                $end = min($iv['end'], $w['end']);

                if ($end > $start) {
                    $result[] = ['start' => $start, 'end' => $end];
                }
            }
        }

        return $this->normalize($result);
    }

    private function subtractIntervals(array $base, array $remove): array {
        $result = [];

        foreach ($base as $b) {
            $currentStart = $b['start'];
            $currentEnd = $b['end'];

            foreach ($remove as $r) {
                if ($r['end'] <= $currentStart || $r['start'] >= $currentEnd) {
                    continue;
                }

                if ($r['start'] > $currentStart) {
                    $result[] = [
                        'start' => $currentStart,
                        'end' => min($r['start'], $currentEnd)
                    ];
                }

                $currentStart = max($currentStart, $r['end']);

                if ($currentStart >= $currentEnd) {
                    break;
                }
            }

            if ($currentStart < $currentEnd) {
                $result[] = [
                    'start' => $currentStart,
                    'end' => $currentEnd
                ];
            }
        }

        return $this->normalize($result);
    }

    private function trimPastIntervals(array $intervals, int $minStart): array {
        $result = [];

        foreach ($intervals as $iv) {
            if ($iv['end'] <= $minStart) {
                continue;
            }

            if ($iv['start'] < $minStart) {
                $iv['start'] = $minStart;
            }

            $result[] = $iv;
        }

        return $result;
    }

    public function insertAppointment(array $data): ?int {
        $stmt = $this->db->prepare("
            INSERT INTO citas (
                uuid,
                paciente,
                asunto,
                forma,
                motivo_consulta,
                fecha,
                h_inicio,
                duracion,
                h_fin,
                estatus,
                registro,
                costo,
                adeudo,
                pagado,
                bonificacion,
                f_registro,
                f_actualizacion
            ) VALUES (
                :uuid,
                :paciente,
                :asunto,
                :forma,
                :motivo_consulta,
                :fecha,
                :h_inicio,
                :duracion,
                :h_fin,
                :estatus,
                :registro,
                :costo,
                :adeudo,
                0,
                0,
                NOW(),
                NOW()
            )");


        $stmt->execute([
            'uuid'                  => $data['uuid'],
            'paciente'              => $data['patient'],
            'asunto'                => $data['appointment_type'],
            'forma'                 => $data['booking_channel'],
            'motivo_consulta'       => $data['chief_complaint'],
            'fecha'                 => $data['appointment_date'],
            'h_inicio'              => $data['appointment_start'],
            'duracion'              => $data['appointment_duration'],
            'h_fin'                 => $data['appointment_end'],
            'estatus'               => $data['status'],
            'registro'              => $data['uid'],
            'costo'                 => $data['appointment_cost'],
            'adeudo'                => $data['appointment_cost'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function insertAppointmentBlock(array $data): void {
        $stmt = $this->db->prepare("
            INSERT INTO citas_bloques (
				uuid,
				cita,
				personal,
				servicio,
				orden,
				h_inicio,
				h_fin,
				duracion,
				estatus
			) VALUES(
                :uuid,
                :cita,
                :personal,
                :servicio,
                :orden,
                :h_inicio,
                :h_fin,
                :duracion,
                :estatus
            )");


        $stmt->execute([
            'uuid'                  => $data['uuid'],
            'cita'                  => $data['appointment'],
            'personal'              => $data['staff'],
            'servicio'              => $data['procedure'],
            'orden'                 => $data['order'],
            'h_inicio'              => $data['start'],
            'h_fin'                 => $data['end'],
            'duracion'              => $data['duration'],
            'estatus'               => $data['status'],
        ]);
    }

    public function insertAppointmentProcedure(array $data): void {
        $stmt = $this->db->prepare("
            INSERT INTO citas_servicios (
				cita,
				servicio,
				personal,
				costo,
				bonificacion
			) VALUES(
                :cita,
                :servicio,
                :personal,
                :costo,
                0
             )");


        $stmt->execute([
            'cita'                  => $data['appointment'],
            'servicio'              => $data['procedure'],
            'personal'              => $data['staff'],
            'costo'                 => $data['cost'],
        ]);
    }
}