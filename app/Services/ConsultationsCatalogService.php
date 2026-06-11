<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\ConsultationCatalogRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class AppointmentsService extends Service
{
    public function __construct(
        private ConsultationCatalogRepository $consultationsCatalogRepository
    ) {
    }
}