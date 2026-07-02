<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class CashReconciliationRepository
{
    public function __construct(private PDO $db) {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getCashReconciliationId(string $uuid): ?int {
        $stmt = $this->db->prepare("
            SELECT id
            FROM cortes
            WHERE uuid = :uuid
            LIMIT 1");
        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }

    public function verifyIfExistsOpen($uid) {
        $stmt = $this->db->prepare("
            SELECT
                c.uuid
            FROM cortes c
            WHERE c.abierta_por = :uid
            AND c.f_cierre IS NULL
            LIMIT 1
        ");

        $stmt->bindParam(':uid', $uid, PDO::PARAM_INT);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        return $id != null ? $id : null;
    }

    public function getCashReconciliationData(array $data) {
        $stmt = $this->db->prepare("
            SELECT
                c.uuid,
                ca.caja,
                opened.uuid opened_by_id,
                opened.nombre opened_by_name,
                COALESCE(DATE_FORMAT(c.f_abierta, '%d/%m/%Y %r'), '') opened_date,
                c.monto_apertura opened_amount,
                closed.uuid closed_by_id,
                closed.nombre closed_by_name,
                COALESCE(DATE_FORMAT(c.f_cierre, '%d/%m/%Y %r'), '') closed_date,
                c.monto_cierre closed_amount,
                c.efectivo_esperado expected_cash,
                c.retiros cash_withdrawals,
                c.depositos cash_deposits,
                c.diferencia cash_difference,
                ce.codigo status_code,
                ce.estatus status,
                c.observaciones,
                COALESCE(DATE_FORMAT(c.f_registro, '%d/%m/%Y %r'), '') registered_date
            FROM cortes c
                LEFT JOIN cajas ca
                    ON c.caja = ca.id
                LEFT JOIN usuarios opened
                    ON c.abierta_por = opened.id
                LEFT JOIN usuarios closed
                    ON c.cerrada_por = closed.id
                LEFT JOIN cortes_estatus ce
                    ON c.estatus = ce.id
            WHERE c.uuid = :uuid
        ");

        $stmt->bindParam(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row;
    }

    public function insert(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO cortes (
                uuid,
                caja,
                abierta_por,
                f_abierta,
                monto_apertura,
                efectivo_esperado,
                diferencia,
                estatus,
                observaciones,
                f_registro
            ) VALUES (
                :uuid,
                :cash_register,
                :initialized_by,
                NOW(),
                :initialize_amount,
                :expected_cash,
                0,
                :status,
                '',
                NOW()
            );
        ");

        $stmt->bindValue(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->bindValue(':cash_register', $data['cash_register'], PDO::PARAM_INT);
        $stmt->bindValue(':initialized_by', $data['uid'], PDO::PARAM_INT);
        $stmt->bindValue(':initialize_amount', $data['initialize_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':expected_cash', $data['initialize_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_INT);
        $stmt->execute();

        return (int) $this->db->lastInsertId();
    }
}