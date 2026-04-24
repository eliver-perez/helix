<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\SpecialtyRepository;
use Throwable;

class SpecialtyController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $repository = new SpecialtyRepository($conn);
            $specialties = $repository->getAll();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'specialties' => $specialties
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener las especialidades.'
            ], 500);
        }
    }
}