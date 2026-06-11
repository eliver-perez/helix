<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\ConsultationCatalogRepository;
use App\Services\ConsultationsCatalogService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class ConsultationsCatalogController extends Controller
{
    private ?ConsultationCatalogRepository $repository = null;

    private function getService(): ConsultationsCatalogService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $consultationsCatalogRepository = new ConsultationCatalogRepository($conn);

        return new ConsultationsCatalogService($consultationsCatalogRepository);
    }

    private function getRepository(): ConsultationCatalogRepository {
        if ($this->repository === null) {
            $database = new Database();
            $conn = $database->getConnection();

            $this->repository = new ConsultationCatalogRepository($conn);
        }

        return $this->repository;
    }

    public function podiatric_sores(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $sore_types = $repository->getPodiatrySoreType();
            $lateralities = $repository->getLaterality();
            $wagner_scale = $repository->getPodiatryWagnerScale();
            $tissue_types = $repository->getPodiatryTissueTypes();
            $evolution_types = $repository->getPodiatryEvolution();
            $exudate_types = $repository->getPodiatryExudateTypes();
            $exudate_colors = $repository->getPodiatryExudateColors();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'sore_types'                => $sore_types,
                        'lateralities'              => $lateralities,
                        'wagner_scale'              => $wagner_scale,
                        'tissue_types'              => $tissue_types,
                        'evolution_types'           => $evolution_types,
                        'exudate_types'             => $exudate_types,
                        'exudate_colors'            => $exudate_colors,
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function podiatric_exploration(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $foot_types = $repository->getPodiatryFootTypes();
            $pulse_types = $repository->getPodiatryPulseTypes();
            $sensitivity_types = $repository->getPodiatrySensitivityTypes();
            $temperature_types = $repository->getPodiatryFootTemperatureTypes();
            $foot_color_types = $repository->getPodiatryFootColorTypes();
            $metatarsal_formulas = $repository->getPodiatryMetatarsalFormula();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'foot_types' => $foot_types,
                        'pulse_types' => $pulse_types,
                        'sensitivity_types' => $sensitivity_types,
                        'temperature_types' => $temperature_types,
                        'foot_color_types' => $foot_color_types,
                        'metatarsal_formulas' => $metatarsal_formulas,
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function podiatry_foot_types(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $foot_types = $repository->getPodiatryFootTypes();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'foot_types' => $foot_types
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function podiatry_pulse_types(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $pulse_types = $repository->getPodiatryPulseTypes();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'pulse_types' => $pulse_types
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function podiatry_sensitivity_types(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $sensitivity_types = $repository->getPodiatrySensitivityTypes();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'sensitivity_types' => $sensitivity_types
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function podiatry_temperature_types(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $temperature_types = $repository->getPodiatryFootTemperatureTypes();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'temperature_types' => $temperature_types
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function podiatry_foot_color_types(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $foot_color_types = $repository->getPodiatryFootColorTypes();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'foot_color_types' => $foot_color_types
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function podiatry_metatarsal_formulas(Request $request, Response $response) {
        try {
            $repository = $this->getRepository();

            $search = trim((string)$this->request->query('search', ''));

            $metatarsal_formulas = $repository->getPodiatryMetatarsalFormula();

            return $response->json([
                    'success' => true,
                    'data' => [
                        'metatarsal_formulas' => $metatarsal_formulas
                    ]
                ], 200);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}