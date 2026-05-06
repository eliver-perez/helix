<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\ConsentTemplatesRepository;
use App\Repositories\TemplatesStatusRepository;
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
        $templatesStatusRepository = new TemplatesStatusRepository($conn);

        return new ConsentTemplatesService($consentTemplatesRepository, $templatesStatusRepository);
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

            return $response->json([
                'status' => 'OK',
                'message' => 'Datos de Plantilla.',
                'data' => [
                    'uuid'                      => $id,
                    'code'                      => $template['codigo'],
                    'name'                      => $template['nombre'],
                    'version'                   => $template['version'],
                    'status_code'               => $template['estatus_codigo'],
                    'status'                    => $template['estatus'],
                    'logo'                      => $template['logo'],
                    'logo_width'                => $template['logo_width'],
                    'line_spacing'              => $template['interlineado'],
                    'font_size'                 => $template['font_size'],
                    'registered_by'             => $template['registro'],
                    'delta'                     => $template['delta'],
                    'registered_date'           => $template['f_registro'],
                    'updated_date'              => $template['f_actualizacion'],
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
                // 'message' => 'No fue posible actualizar la plantilla.'
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function preview(Request $request, Response $response) {
        try {
            $service = $this->getService();

            $pdfContent = $service->previewPdf([
                'delta'             => $request->input('delta', ''),
                'font_size'         => $request->input('font_size', 9),
                'line_height'       => $request->input('line_height', 1.2),

                'logo'              => $_FILES['logo'] ?? null,
                'logo_width'        => $request->input('logo_width', 35),
            ]);

            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="preview-consentimiento.pdf"');
            header('Content-Length: ' . strlen($pdfContent));

            echo $pdfContent;
            exit;

        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'Error al generar la vista previa del consentimiento.'
            ], 500);
        }
    }

    public function status(Request $request, Response $response, string $id) {
        try {
            $service = $this->getService();

            $data = $service->getTemplateStatus([
                'uuid'              => $id,
            ]);

            return $response->json([
                'status' => 'OK',
                'message' => 'Estatus de Plantilla.',
                'data' => [
                    'uuid'                      => $id,
                    'code'                      => $data['codigo'] ?? '',
                    'status'                    => $data['estatus'] ?? '',
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
                'message' => 'Error al generar la vista previa del consentimiento.'
            ], 500);
        }
    }

    public function activate(Request $request, Response $response, string $id) {
        try {
            $currentUserId = Auth::id();

            if($currentUserId === null) {
                throw new RuntimeException("No autenticado.");
            }

            $service = $this->getService();

            $service->activate([
                'uuid'                      => $id,
                'uid'                       => $currentUserId,
            ]);

            return $response->json([
                'status' => 'OK',
                'message' => 'Plantilla activada correctamente.',
                'data' => [
                    'uuid'                  => $id
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
                // 'message' => 'No fue posible activar la plantilla.'
                'message' => $e->getMessage()
            ], 500);
        }
    }
}