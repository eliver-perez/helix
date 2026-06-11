<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\SoresRepository;
use App\Services\SoresService;
use Throwable;
use InvalidArgumentException;
use RuntimeException;

class SoresController extends Controller
{
    private ?SoresRepository $repository = null;

    private function getService(): SoresService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $soresRepository = new SoresRepository($conn);

        return new SoresService($soresRepository);
    }

    private function getRepository(): SoresRepository {
        if ($this->repository === null) {
            $database = new Database();
            $conn = $database->getConnection();

            $this->repository = new SoresRepository($conn);
        }

        return $this->repository;
    }

    public function sores(Request $request, Response $response) {
        try {
            $service = $this->getService();

            $sores = $service->getSores();

            return $response->json([
                'success' => true,
                'data' => [
                    'sores' => $sores
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