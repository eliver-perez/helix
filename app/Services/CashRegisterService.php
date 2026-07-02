<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\CashRegisterRepository;
use App\Repositories\SettingsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class CashRegisterService extends Service
{
    public function __construct(
        private CashRegisterRepository $cashRegisterRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAll() {
        try {
            $data = $this->cashRegisterRepository->getAll();
            $cash_registers = array();

            foreach($data as $d) {
                array_push($cash_registers, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'code'                      => $d['codigo'],
                    'register'                  => $d['caja'],
                ));
            }

            return $cash_registers;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}