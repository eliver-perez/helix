<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\ProceduresRepository;
use Throwable;
use InvalidArgumentException;
use RuntimeException;

class ProceduresController extends Controller
{
    private ?ProceduresRepository $repository = null;

    private function getRepository(): ProceduresRepository {
        if ($this->repository === null) {
            $database = new Database();
            $conn = $database->getConnection();

            $this->repository = new ProceduresRepository($conn);
        }

        return $this->repository;
    }

    public function index(Request $request, Response $response)
    {
        try {
            $repository = $this->getRepository();

            $procedures = $repository->getAll();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'procedures' => $procedures
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los géneros.'
            ], 500);
        }
    }

    public function staff(Request $request, Response $response, int $procedure) {
        try {
            $repository = $this->getRepository();

            if(!$procedure)
                throw new InvalidArgumentException('No se recibio procedimiento');

            $staff = $repository->getProcedureStaff($procedure);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'staff' => $staff
                ]
            ]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los géneros.'
            ], 500);
        }
    }

    public function procedureStaffData(Request $request, Response $response, int $procedure, int $staff) {
        try {
            $repository = $this->getRepository();

            if(!$procedure)
                throw new InvalidArgumentException('No se recibio procedimiento');

            if(!$staff)
                throw new InvalidArgumentException('No se recibio id de personal');

            $data = $repository->getProcedureStaffData($procedure, $staff);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'id' => $data['id'],
                    'procedimiento_id' => $data['procedimiento_id'],
                    'procedimiento' => $data['procedimiento'],
                    'personal_id' => $data['personal_id'],
                    'nombre' => $data['nombre'],
                    'duracion' => $data['duracion_min'],
                    'costo' => $data['costo']
                ]
            ]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                // 'message' => 'No fue posible obtener datos del servicio/procedimiento.'
            ], 500);
        }
    }
}