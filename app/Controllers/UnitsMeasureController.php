<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\UnitsMeasureRepository;
use Throwable;

class UnitsMeasureController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $repository = new UnitsMeasureRepository($conn);
            $units_measure = $repository->getAll();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'units_measure' => $units_measure
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los géneros.'
            ], 500);
        }
    }
}