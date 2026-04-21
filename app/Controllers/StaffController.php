<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Staff;

use App\Core\Request;
use App\Core\Response;

class StaffController extends Controller
{
    private Staff $staffModel;

    public function __construct()
    {
        parent::__construct();
        $this->staffModel = new Staff();
    }

    public function index(Request $request, Response $response): void
    {
        $auth = new AuthMiddleware();
        $result = $auth->handle($request, $response);
        if ($result !== true) {
            return;
        }
        $search = trim((string)$this->request->query('search', ''));

        $rows = $this->staffModel->getAll($search !== '' ? $search : null);

        $response->json([
            'status' => 'OK',
            'data' => []
        ], 200);
        // $this->success($rows, 'Listado de personal obtenido correctamente');
    }

    public function show(Request $request, Response $response, string $id): void
    {
        $auth = new AuthMiddleware();
        $result = $auth->handle($request, $response);
        if ($result !== true) {
            return;
        }

        if (!ctype_digit($id)) {
            $this->error('El identificador es inválido', 422);
            return;
        }

        $row = $this->staffModel->findById((int)$id);

        if ($row === null) {
            $this->error('Empleado no encontrado', 404);
            return;
        }

        // $this->success($row, 'Empleado obtenido correctamente');
        $response->json([
            'status' => 'OK',
            'id' => (int)$id
        ], 200);
    }
}