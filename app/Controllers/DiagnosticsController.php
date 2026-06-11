<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\DiagnosticsRepository;
use App\Services\DiagnosticsService;
use Throwable;
use InvalidArgumentException;
use RuntimeException;

class DiagnosticsController extends Controller
{
    private ?DiagnosticsRepository $repository = null;

    private function getService(): DiagnosticsService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $diagnosticsRepository = new DiagnosticsRepository($conn);

        return new DiagnosticsService($diagnosticsRepository);
    }

    private function getRepository(): DiagnosticsRepository {
        if ($this->repository === null) {
            $database = new Database();
            $conn = $database->getConnection();

            $this->repository = new DiagnosticsRepository($conn);
        }

        return $this->repository;
    }

    public function diagnostics(Request $request, Response $response) {
        try {
            $service = $this->getService();

            $diagnostics = $service->getDiagnostics();

            return $response->json([
                'success' => true,
                'data' => [
                    'diagnostics' => $diagnostics
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function diagnostic_types(Request $request, Response $response) {
        try {
            $service = $this->getService();

            $diagnostic_types = $service->getDiagnosticTypes();

            return $response->json([
                'success' => true,
                'data' => [
                    'diagnostic_types' => $diagnostic_types
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}