<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\UserRoleRepository;
use Throwable;

class UserRoleController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $repository = new UserRoleRepository($conn);
            $roles = $repository->getAll();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'roles' => $roles
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los roles.'
            ], 500);
        }
    }
}