<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\LocationRepository;
use App\Services\LocationService;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class LocationController extends Controller
{
    private function getService(): LocationService
    {
        $database = new Database();
        $conn = $database->getConnection();

        $repository = new LocationRepository($conn);

        return new LocationService($repository);
    }

    public function countries(Request $request, Response $response)
    {
        try {
            $service = $this->getService();

            $search = $request->query('search');

            $countries = $service->getCountries($search);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'countries' => $countries
                ]
            ]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los países.'
            ], 500);
        }
    }

    public function states(Request $request, Response $response)
    {
        try {
            $service = $this->getService();

            $countryId = $request->queryInt('id');

            $search = $request->query('search');

            $states = $service->getStates($countryId, $search);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'states' => $states
                ]
            ]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                // 'message' => 'No fue posible obtener los estados.'
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function municipalities(Request $request, Response $response)
    {
        try {
            $service = $this->getService();

            $stateId = $request->queryInt('id');

            $search = $request->query('search');

            $municipalities = $service->getMunicipalities($stateId, $search);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'municipalities' => $municipalities
                ]
            ]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los municipios.'
            ], 500);
        }
    }

    public function localities(Request $request, Response $response)
    {
        try {
            $service = $this->getService();

            $municipalityId = $request->queryInt('id');

            $search = $request->query('search');

            $localities = $service->getLocalities($municipalityId, $search);

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'localities' => $localities
                ]
            ]);
        } catch (InvalidArgumentException | RuntimeException $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ], 400);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener las colonias.'
            ], 500);
        }
    }

    public function storeState(Request $request, Response $response)
    {
        try {
            $service = $this->getService();

            $input = $this->getJsonInput();

            $stateId = $service->createState($input);

            return $response->json([
                'status' => 'OK',
                'message' => 'Estado registrado correctamente.',
                'data' => [
                    'state_id' => $stateId
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
                'message' => 'No fue posible registrar el estado.'
            ], 500);
        }
    }

    public function storeMunicipality(Request $request, Response $response)
    {
        try {
            $service = $this->getService();

            $input = $this->getJsonInput();

            $municipalityId = $service->createMunicipality($input);

            return $response->json([
                'status' => 'OK',
                'message' => 'Municipio registrado correctamente.',
                'data' => [
                    'municipality_id' => $municipalityId
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
                'message' => 'No fue posible registrar el municipio.'
            ], 500);
        }
    }

    public function storeLocality(Request $request, Response $response)
    {
        try {
            $service = $this->getService();

            $input = $this->getJsonInput();

            $localityId = $service->createLocality($input);

            return $response->json([
                'status' => 'OK',
                'message' => 'Colonia registrada correctamente.',
                'data' => [
                    'locality_id' => $localityId
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
                'message' => 'No fue posible registrar la colonia.'
            ], 500);
        }
    }

    private function getJsonInput(): array
    {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!is_array($data)) {
            throw new InvalidArgumentException('El cuerpo de la petición no contiene un JSON válido.');
        }

        return $data;
    }
}