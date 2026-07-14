<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\CashReconciliationRepository;
use App\Repositories\CashReconciliationStatusRepository;
use App\Repositories\CashRegisterRepository;
use App\Repositories\PaymentsRepository;
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
        private PaymentsRepository $paymentsRepository,
        private SettingsRepository $settingsRepository
    ) {
    }

    public function getAll(array $data): array {
        try {
            $data = $this->cashReconciliationRepository->getAll([
                'search'            => $data['search'] !== '' ? $data['search'] : null,
                'limit'             => $data['limit'],
                'offset'            => $data['offset'],
                'uid'               => $data['uid'],
            ]);
            $cash_reconciliation = array();

            foreach($data as $d) {
                array_push($cash_reconciliation, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'folio'                     => $d['folio'] ?? 'S/F',
                    'opened_by'                 => $d['abierto_por'] ?? '',
                    'opened_date'               => $d['f_abierta'] ?? '',
                    'closed_date'               => $d['f_cierre'] ?? '',
                    'total'                     => $d['total_venta'] ?? 0,
                ));
            }

            return $cash_reconciliation;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    function getCashReconciliation($data): ?array {
        try {
            $uuid = $this->normalizeRequiredText(
                $data['uuid'] ?? null,
                'Error al recibir identificador del corte.'
            );

            $cash_reconciliation_data = $this->cashReconciliationRepository->getCashReconciliationData([
                'uuid'                          => $this->uuidStringtoBinary($uuid)
            ]);

            if(!$cash_reconciliation_data)
                throw new RuntimeException("Ocurrio un error al intentar obtener la información.");

            $cash_reconciliation_payments_data = $this->paymentsRepository->getCashReconciliationPayments([
                'uuid'                          => $this->uuidStringtoBinary($uuid),
                'search'                        => $data['search'] !== '' ? $data['search'] : null,
                'limit'                         => $data['limit'],
                'offset'                        => $data['offset'],
            ]);

            $cash_reconciliation_payments = array();
            foreach($cash_reconciliation_payments_data as $p) {
                array_push($cash_reconciliation_payments, array(
                    'id'                            => $this->uuidBinarytoString($p['uuid']),
                    'folio'                         => $p['folio'],
                    'consecutivo'                   => $p['consecutivo'],
                    'client'                        => $p['cliente'],
                    'payment_method'                => $p['metodo_pago'],
                    'amount'                        => $p['monto_pago'],
                    'registered_by'                 => $p['registro'],
                    'status'                        => $p['estatus'],
                    'payment_date'                  => $p['f_pago'],
                    'registered_date'               => $p['f_registro'],
                    'update_date'                   => $p['f_actualizacion']
                ));
            }
            
            return [
                'id'                                => $this->uuidBinarytoString($cash_reconciliation_data['uuid']),
                'folio'                             => $cash_reconciliation_data['folio'],
                'registrar'                         => $cash_reconciliation_data['caja'],
                'opened_by_id'                      => $cash_reconciliation_data['opened_by_id'] != null ? $this->uuidBinarytoString($cash_reconciliation_data['opened_by_id']) : '',
                'opened_by_name'                    => $cash_reconciliation_data['opened_by_name'],
                'opened_date'                       => $cash_reconciliation_data['opened_date'],
                'opened_amount'                     => $cash_reconciliation_data['opened_amount'],
                'closed_by_id'                      => $cash_reconciliation_data['closed_by_id'] != null ? $this->uuidBinarytoString($cash_reconciliation_data['closed_by_id']) : '',
                'closed_by_name'                    => $cash_reconciliation_data['closed_by_name'],
                'closed_date'                       => $cash_reconciliation_data['closed_date'],
                'closed_amount'                     => $cash_reconciliation_data['closed_amount'],
                'other_payment_methods'             => $cash_reconciliation_data['other_payment_methods'],
                'cash'                              => $cash_reconciliation_data['cash'],
                'total_sale'                        => $cash_reconciliation_data['total_sale'],
                'expected_cash'                     => $cash_reconciliation_data['expected_cash'],
                'cash_withdrawals'                  => $cash_reconciliation_data['cash_withdrawals'],
                'cash_deposits'                     => $cash_reconciliation_data['cash_deposits'],
                'cash_difference'                   => $cash_reconciliation_data['cash_difference'],
                'cancelled'                         => $cash_reconciliation_data['cancelled'],
                'status_code'                       => $cash_reconciliation_data['status_code'],
                'status'                            => $cash_reconciliation_data['status'],
                'observations'                      => $cash_reconciliation_data['observaciones'],
                'registered_date'                   => $cash_reconciliation_data['registered_date'],
                'payments'                          => $cash_reconciliation_payments,
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function create(array $data): ?string {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');
        $cash_register = $this->normalizeRequiredText($data['cash_register'] ?? null, 'Es necesario seleccionar una caja.');
        $initialize_amount = $this->normalizeRequiredFloat($data['initialize_amount'] ?? null, 'Es necesario capturar el monto de inicio.');

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

    public function close(array $data): ?string {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');
        $id = $this->normalizeRequiredText($data['id'] ?? null, 'No se tiene informacion de un corte activo.');
        $closing_amount = $this->normalizeRequiredFloat($data['closing_amount'] ?? null, 'Es necesario capturar el monto de cierre.');
        $observations = $this->normalizeOptionalText($data['observations'] ?? '');

        if(!$this->is_uuid($id))
            throw new \RuntimeException("Identificador no valido.");

        $conn = $this->cashReconciliationRepository->getConnection();
        $conn->beginTransaction();
        try {
            $cashReconciliationUuid = $this->cashReconciliationRepository->verifyIfExistsOpen($uid);
            if($cashReconciliationUuid != null) {
                $active_id = $this->uuidBinarytoString($cashReconciliationUuid);
                if($id != $active_id)
                    throw new \RuntimeException("Identificador de corte no coincide con activo actual.");

                $cashReconciliationClosedStatusId = $this->cashReconciliationStatusRepository->getIdByCode('closed');
                $this->cashReconciliationRepository->close([
                        'uuid'                          => $cashReconciliationUuid,
                        'status'                        => $cashReconciliationClosedStatusId,
                        'amount'                        => $closing_amount,
                        'observations'                  => $observations,
                        'uid'                           => $uid,
                    ]);
            }
            $conn->commit();
            return $id;
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

    public function cashReconciliationClosingData($uid) {
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
                    return [
                        'id'                                                => $this->uuidBinarytoString($cashReconciliationData['uuid']),
                        'opened_amount'                                     => $cashReconciliationData['opened_amount'],
                        'other_payment_methods'                             => $cashReconciliationData['other_payment_methods'],
                        'cash'                                              => $cashReconciliationData['cash'],
                        'total_sale'                                        => $cashReconciliationData['total_sale'],
                        'expected_cash'                                     => $cashReconciliationData['expected_cash'],
                        'cash_withdrawals'                                  => $cashReconciliationData['cash_withdrawals'],
                        'cash_deposits'                                     => $cashReconciliationData['cash_deposits'],
                        'cash_difference'                                   => $cashReconciliationData['cash_difference'],
                        'cancelled'                                         => $cashReconciliationData['cancelled'],
                    ];
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