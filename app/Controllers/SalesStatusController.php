<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\SalesStatusRepository;
use Throwable;

class SalesStatusController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $repository = new SalesStatusRepository($conn);
            $sales_status = $repository->getAll();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'sales_status' => $sales_status
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los estatus de venta.'
            ], 500);
        }
    }
}