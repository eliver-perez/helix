<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\SalesRepository;
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
}