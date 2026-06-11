<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\SoresRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class SoresService extends Service
{
    public function __construct(
        private SoresRepository $soresRepository
    ) {
    }
}