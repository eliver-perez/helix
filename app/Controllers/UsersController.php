<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\UsersRepository;
use App\Services\UsersService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class UsersController extends Controller
{
    private ?UsersRepository $repository = null;

    private function getService(): UsersService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $usersRepository = new UsersRepository($conn);

        return new UsersService($usersRepository);
    }

    private function getRepository(): UsersRepository {
        if ($this->repository === null) {
            $database = new Database();
            $conn = $database->getConnection();

            $this->repository = new UsersRepository($conn);
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
                        'users' => $data
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
                'message' => 'No fue posible obtener los usuarios.'
                // 'message' => $e->getMessage()
            ], 500);
        }
    }
}