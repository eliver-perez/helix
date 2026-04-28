<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\AppointmentsRepository;
use App\Repositories\AppointmentsTypesRepository;
use App\Repositories\BookingChannelsRepository;
use App\Repositories\AppointmentsStatusRepository;
use App\Repositories\SettingsRepository;
use App\Services\AppointmentsService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class AppointmentsController extends Controller
{
    private function getService(): AppointmentsService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $appointmentsRepository = new AppointmentsRepository($conn);
        $appointmentsTypeRepository = new AppointmentsTypesRepository($conn);
        $bookingChannelsRepository = new BookingChannelsRepository($conn);
        $appointmentsStatusRepository = new AppointmentsStatusRepository($conn);
        $settingsRepository = new SettingsRepository($conn);

        return new AppointmentsService($appointmentsRepository, $appointmentsTypeRepository, $bookingChannelsRepository, $appointmentsStatusRepository, $settingsRepository);
    }

    private function getRepository(): AppointmentsRepository {
        if ($this->repository === null) {
            $database = new Database();
            $conn = $database->getConnection();

            $this->repository = new AppointmentsRepository($conn);
        }

        return $this->repository;
    }

    public function index(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $data = $repository->getAll($search !== '' ? $search : null);

            return $response->json([
                    'status' => 'OK',
                    'data' => [
                        'patients' => $data
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los paciente.'
                // 'message' => $e->getMessage()
            ], 500);
        }
    }

    public function availableSlots(Request $request, Response $response) {
        try {
            $service = $this->getService();

            $date = trim((string)$this->request->input('date', ''));
            $proceduresRaw = $request->input('procedures', '[]');
            $procedures = json_decode((string)$proceduresRaw, true);

            if ($date === '' || !is_array($procedures) || count($procedures) === 0) {
                return $response->json([
                    'status' => 'ERROR',
                    'message' => 'Información Incompleta'
                ], 400);
            }

            $slots = $service->calculateAppointmentAvailability($date, $procedures);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'date' => $date,
                    'slots' => $slots
                ]
            ], 200);

        } catch (InvalidArgumentException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function calendar(Request $request, Response $response) {
        try {
            $service = $this->getService();
            
            $start = trim((string)$this->request->query('start', ''));
            $end = trim((string)$this->request->query('end', ''));

            if ($start === '' || $end === '') {
                return $response->json([
                    'status' => 'ERROR',
                    'message' => 'Información Incompleta'
                ], 400);
            }

            $appointments = $service->getCalendarAppointments($start, $end);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'appointments' => $appointments
                ]
            ]);
        } catch (InvalidArgumentException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function schedule(Request $request, Response $response) {
        try {
            $currentUserId = Auth::id();

            if($currentUserId === null) {
                throw new RuntimeException("No autenticado.");
            }

            $service = $this->getService();

            $patient = (int)$request->input('patient', 0);
            $appointment_type = (int)$request->input('appointment_type', 0);
            $booking_channel = (int)$request->input('booking_channel', 0);
            $appointmentRaw = $request->input('appointment', '[]');
            $appointment = json_decode((string)$appointmentRaw, true);
            $chief_complaint = trim((string)$this->request->input('chief_complaint', ''));

            // die(var_dump($appointment));

            if (!is_array($appointment) || count($appointment) === 0 || $patient === 0 || $appointment_type === 0 || $booking_channel === 0) {
                return $response->json([
                    'status' => 'ERROR',
                    'message' => 'Información Incompleta'
                ], 400);
            }

            $appointmentId = $service->scheduleAppointment([
                    'patient'           => $patient,
                    'appointment_type'  => $appointment_type,
                    'booking_channel'   => $booking_channel,
                    'appointment'       => $appointment,
                    'chief_complaint'   => $chief_complaint,
                    'uid'               => $currentUserId
                ]);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'id' => $appointmentId
                ]
            ], 200);

        } catch (InvalidArgumentException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);

        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}