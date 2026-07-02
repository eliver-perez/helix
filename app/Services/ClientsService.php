<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\ClientsRepository;
use App\Repositories\GenderRepository;
use App\Repositories\LocationRepository;
use App\Repositories\BillingRepository;
use App\Repositories\SettingsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class ClientsService extends Service
{
    public function __construct(
        private ClientsRepository $clientsRepository,
        private GenderRepository $genderRepository,
        private LocationRepository $locationRepository,
        private BillingRepository $billingRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAll(?string $search = null, int $limit = 10, int $offset = 0): array {
        try {
            $data = $this->clientsRepository->getAll($search !== '' ? $search : null,
                $limit,
                $offset
            );
            $clients = array();

            foreach($data as $d) {
                array_push($clients, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'code'                      => $d['clave'],
                    'name'                      => $d['nombre'],
                    'dob'                       => $d['f_nacimiento'],
                    'gender'                    => $d['genero'],
                    'address'                   => $d['domicilio'],
                    'phone'                     => $d['telefono'],
                    'mobile'                    => $d['movil'],
                    'email'                     => $d['email'],
                    'registered_date'           => $d['f_registro'],
                    'last_payment_date'         => $d['ultimo_pago'],
                ));
            }

            return $clients;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}