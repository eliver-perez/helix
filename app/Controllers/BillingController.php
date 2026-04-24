<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\BillingRepository;
use Throwable;

class BillingController extends Controller
{
    public function regimenes(Request $request, Response $response)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $repository = new BillingRepository($conn);
            $regimenes = $repository->getRegimenes();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'regimenes' => $regimenes
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los regimenes.'
            ], 500);
        }
    }
}