<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\CashReconciliationRepository;
use App\Repositories\CashReconciliationStatusRepository;
use App\Repositories\CashRegisterRepository;
use App\Repositories\SettingsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class CashReconciliationService extends Service
{
    public function __construct(
        private CashReconciliationRepository $cashReconciliationRepository,
        private CashReconciliationStatusRepository $cashReconciliationStatusRepository,
        private CashRegisterRepository $cashRegisterRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAll() {
        
    }

    public function create(array $data): ?string {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');
        $cash_register = $this->normalizeRequiredText($data['cash_register'] ?? null, 'Es necesario seleccionar una caja.');
        $initialize_amount = $this->normalizeRequiredInt($data['initialize_amount'] ?? null, 'Es necesario capturar el monto de inicio.');

        $conn = $this->cashReconciliationRepository->getConnection();
        $conn->beginTransaction();
        try {
            $cashReconciliationUuid = $this->cashReconciliationRepository->verifyIfExistsOpen($uid);
            if($cashReconciliationUuid == null) {
                $cashReconciliationStatusId = $this->cashReconciliationStatusRepository->getIdByCode('open');
                $cashRegisterId = $this->cashRegisterRepository->getIdByUuid($this->uuidStringtoBinary($cash_register));
                $cashReconciliationUuid = $this->generateUuidBinary();
                $cashReconciliationId = $this->cashReconciliationRepository->insert([
                        'uuid'                          => $cashReconciliationUuid,
                        'cash_register'                 => $cashRegisterId,
                        'initialize_amount'             => $initialize_amount,
                        'status'                        => $cashReconciliationStatusId,
                        'uid'                           => $uid,
                    ]);
            }
            
            $conn->commit();

            $_SESSION['cash_reconciliation_id'] = $this->uuidBinarytoString($cashReconciliationUuid);
            
            return $_SESSION['cash_reconciliation_id'];
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }

    public function checkForActiveCashReconciliation($uid) {
        try {
            if(!isset($_SESSION['cash_reconciliation']['id']) || $_SESSION['cash_reconciliation']['id'] == null)
                $cashReconciliationUuid = $this->cashReconciliationRepository->verifyIfExistsOpen($uid);
            else
                $cashReconciliationUuid = $this->uuidStringtoBinary($_SESSION['cash_reconciliation']['id']);
            if($cashReconciliationUuid != null) {
                $cashReconciliationData = $this->cashReconciliationRepository->getCashReconciliationData([
                    'uuid'                      => $cashReconciliationUuid
                ]);
                if($cashReconciliationData != null) {
                    if($cashReconciliationData['status_code'] == 'open') {
                        $this->setCashReconciliationData($this->uuidBinarytoString($cashReconciliationData['uuid']),
                                                    $this->uuidBinarytoString($cashReconciliationData['opened_by_id']),
                                                    $cashReconciliationData['opened_by_name'],
                                                    $cashReconciliationData['opened_date']);
                        return [
                            'id'                                => $this->uuidBinarytoString($cashReconciliationData['uuid']),
                            'opened_by_id'                      => $this->uuidBinarytoString($cashReconciliationData['opened_by_id']),
                            'opened_by_name'                    => $cashReconciliationData['opened_by_name'],
                            'opened_date'                       => $cashReconciliationData['opened_date'],
                        ];
                    } else {
                        $this->setCashReconciliationData();
                    }
                } else {
                    $this->setCashReconciliationData();
                }
                return null;
            } else {
                return null;
            }
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function setCashReconciliationData($id = null,
                                                $opened_by = null,
                                                $opened_by_name = null,
                                                $opened_date = null) {
        if(!isset($_SESSION['cash_reconciliation'])) {
            $_SESSION['cash_reconciliation'] = [];
        }
        $_SESSION['cash_reconciliation']['id'] = $id;
        $_SESSION['cash_reconciliation']['opened_by'] = $opened_by;
        $_SESSION['cash_reconciliation']['opened_by_name'] = $opened_by_name;
        $_SESSION['cash_reconciliation']['opened_date'] = $opened_date;
    }
}