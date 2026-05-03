<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\ConsentTemplatesRepository;
use App\Services\ConsentTemplatesService;
use Throwable;

class ConsentTemplatesController extends Controller
{
    private ?ConsentTemplatesRepository $repository = null;

    private function getService(): ConsentTemplatesService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $consentTemplatesRepository = new ConsentTemplatesRepository($conn);

        return new ConsentTemplatesService($consentTemplatesRepository);
    }

    private function getRepository(): ConsentTemplatesRepository {
        if ($this->repository === null) {
            $database = new Database();
            $conn = $database->getConnection();

            $this->repository = new ConsentTemplatesRepository($conn);
        }

        return $this->repository;
    }

    public function index(Request $request, Response $response) {
        try {
            $service = $this->getService();

            $templates = $service->getAllTemplates();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'templates' => $templates
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage(),
                // 'message' => 'No fue posible obtener las plantillas.'
            ], 500);
        }
    }

    public function store(Request $request, Response $response) {
        try {
            $currentUserId = Auth::id();

            if($currentUserId === null) {
                throw new RuntimeException("No autenticado.");
            }

            $service = $this->getService();

            $templateId = $service->create([
                'code'                      => $request->input('code'),
                'template_name'             => $request->input('template_name'),

                'uid'                       => $currentUserId,
            ]);

            return $response->json([
                'status' => 'OK',
                'message' => 'Plantilla registrada correctamente.',
                'data' => [
                    'template_id' => $templateId
                ]
            ], 201);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                // 'message' => 'No fue posible registrar el personal.'
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, Response $response, string $id) {
        try {
            $service = $this->getService();

            $template = $service->getTemplate([
                'uuid'                      => $id,
            ]);
            // die(var_dump($template));

            return $response->json([
                'status' => 'OK',
                'message' => 'Datos de Plantilla.',
                'data' => [
                    'template' => $template
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
                // 'message' => 'No fue posible registrar el personal.'
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Response $response, string $id) {
        try {
            $currentUserId = Auth::id();

            if($currentUserId === null) {
                throw new RuntimeException("No autenticado.");
            }

            $service = $this->getService();

            $templateId = $service->update([
                'uuid'                      => $id,
                'template_html'             => $request->input('template_html'),
                'template_delta'            => $request->input('template_delta'),
                'uid'                       => $currentUserId,
            ]);

            return $response->json([
                'status' => 'OK',
                'message' => 'Plantilla registrada correctamente.',
                'data' => [
                    'template_id' => $templateId
                ]
            ], 201);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                // 'message' => 'No fue posible registrar el personal.'
                'message' => $e->getMessage()
            ], 500);
        }
    }
}