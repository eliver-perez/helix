<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\AppointmentsRepository;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class AppointmentsController extends Controller
{
    private function getService(): AppointmentsService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $patientsRepository = new AppointmentsRepository($conn);

        return new AppointmentsService($patientsRepository);
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
}