<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\SalesRepository;
use App\Repositories\SalesStatusRepository;
use App\Repositories\AppointmentsRepository;
use App\Repositories\AppointmentsTypesRepository;
use App\Repositories\BookingChannelsRepository;
use App\Repositories\AppointmentsStatusRepository;
use App\Repositories\PatientsRepository;
use App\Repositories\StaffRepository;
use App\Repositories\ProceduresRepository;
use App\Repositories\SettingsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class SalesService extends Service
{
    public function __construct(
        private SalesRepository $salesRepository,
        private SalesStatusRepository $salesStatusRepository,
        private AppointmentsRepository $appointmentsRepository,
        private AppointmentsTypesRepository $appointmentsTypesRepository,
        private BookingChannelsRepository $bookingChannelRepository,
        private AppointmentsStatusRepository $appointmentsStatusRepository,
        private PatientsRepository $patientsRepository,
        private StaffRepository $staffRepository,
        private ProceduresRepository $proceduresRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAll(?string $search = null, int $limit = 10, int $offset = 0, string $status = ''): array {
        try {
            if($status != '')
                $status_id = $this->salesStatusRepository->getIdByCode($status);
            else
                $status_id = 0;

            $data = $this->salesRepository->getAll($search !== '' ? $search : null,
                $limit,
                $offset,
                $status_id,
            );
            $sales = array();

            foreach($data as $d) {
                array_push($sales, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'name'                      => $d['nombre'],
                    'subtotal'                  => $d['subtotal'],
                    'taxes'                     => $d['impuestos'],
                    'total'                     => $d['total'],
                    'discount'                  => $d['descuento'],
                    'payed'                     => $d['pagado'],
                    'debt'                      => $d['adeudo'],
                    'sale_date'                 => $d['f_venta'],
                    'registered_date'           => $d['f_registro'],
                    'last_visit_date'           => $d['f_ultima_visita'],
                ));
            }

            return $sales;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}