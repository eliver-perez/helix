<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class PaymentsRepository
{
    public function __construct(private PDO $db) {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getAll(?string $search = null, int $limit = 10, int $offset = 0, int $status = 0): array {
        $sql = "
            SELECT
                v.id,
                v.uuid,
                CASE WHEN p.id IS NOT NULL
                    THEN TRIM(
                                CONCAT(
                                    p.nombre, ' ',
                                    COALESCE(p.paterno, ''), ' ',
                                    COALESCE(p.materno, '')
                                )
                            )
                    ELSE TRIM(
                                CONCAT(
                                    c.nombre, ' ',
                                    COALESCE(c.paterno, ''), ' ',
                                    COALESCE(c.materno, '')
                                )
                            )
                END nombre,
                v.subtotal,
                v.impuestos,
                v.total,
                v.descuento,
                v.pagado,
                v.adeudo,
                COALESCE(DATE_FORMAT(v.f_venta, '%d/%m/%Y %r'), '') f_venta,
                COALESCE(DATE_FORMAT(p.f_registro, '%d/%m/%Y %r'), '') f_registro,
                COALESCE(DATE_FORMAT(p.f_ultima_visita, '%d/%m/%Y %r'), '') f_ultima_visita
            FROM ventas v
                LEFT JOIN pacientes p
                    ON v.paciente = p.id
                LEFT JOIN clientes c
                    ON v.cliente = c.id
            WHERE 1 = 1 
        ";

        $params = [];

        $fields = ['p.clave', 'p.nombre', 'p.paterno', 'p.materno', 'c.clave', 'c.nombre', 'c.paterno', 'c.materno'];

        $conditions = [];
        $params = [];

        foreach ($fields as $i => $field) {
            $param = "search_$i";
            $conditions[] = "$field LIKE :$param";
            $params[$param] = '%' . $search . '%';
        }

        $sql .= " AND (" . implode(' OR ', $conditions) . ")";

        if($status != 0)
            $sql .= "
                AND v.estatus = :status";

        $sql .= "
            ORDER BY nombre ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        if($status != 0)
            $stmt->bindValue(':status', $status, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function registerPayment(array $data) {
        $sql = "
            INSERT INTO pagos (
                uuid,
                folio,
                consecutivo,
                cliente,
                corte,
                metodo_pago,
                referencia,
                observaciones,
                adeudo_anterior,
                monto_pago,
                adeudo,
                estatus,
                registro,
                f_pago,
                f_registro
            ) VALUES (
                :uuid,
                :folio,
                :consecutive,
                :client,
                :cash_reconciliation,
                :payment_method,
                :reference,
                '',
                :balance_due_before,
                :payment_amount,
                :balance_due,
                1,
                :uid,
                NOW(),
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->bindValue(':folio', $data['folio'], PDO::PARAM_STR);
        $stmt->bindValue(':consecutive', $data['consecutive'], PDO::PARAM_INT);
        $stmt->bindValue(':client', $data['client'], PDO::PARAM_INT);
        $stmt->bindValue(':cash_reconciliation', $data['cash_reconciliation'], PDO::PARAM_INT);
        $stmt->bindValue(':payment_method', $data['payment_method'], PDO::PARAM_INT);
        $stmt->bindValue(':reference', $data['reference'], PDO::PARAM_STR);
        $stmt->bindValue(':balance_due_before', $data['balance_due_before'], PDO::PARAM_STR);
        $stmt->bindValue(':payment_amount', $data['payment_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':balance_due', $data['balance_due'], PDO::PARAM_STR);
        $stmt->bindValue(':uid', $data['uid'], PDO::PARAM_INT);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        return $this->db->lastInsertId();
    }
    
    public function registerSalePayment(array $data) {
        $sql = "
            INSERT INTO pagos_ventas (
                uuid,
                pago,
                venta,
                adeudo_anterior,
                monto_pago,
                adeudo_actual,
                f_registro
            )
            SELECT
                :uuid,
                :payment,
                v.id,
                :balance_due_before,
                :payment_amount,
                :balance_due,
                NOW()
            FROM ventas v
            WHERE v.uuid = :sale_uuid
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->bindValue(':payment', $data['payment'], PDO::PARAM_INT);
        $stmt->bindValue(':sale_uuid', $data['sale'], PDO::PARAM_LOB);
        $stmt->bindValue(':balance_due_before', $data['balance_due_before'], PDO::PARAM_STR);
        $stmt->bindValue(':payment_amount', $data['payment_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':balance_due', $data['balance_due'], PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        return $this->db->lastInsertId();
    }
    
    public function registerSaleDetailPayment(array $data) {
        $sql = "
            INSERT INTO pagos_ventas_detalles (
                uuid,
                pago,
                venta,
                venta_detalle,
                adeudo_anterior,
                monto_pago,
                adeudo_actual
            )
            SELECT
                :uuid,
                :payment,
                vd.venta,
                vd.id,
                :balance_due_before,
                :payment_amount,
                :balance_due
            FROM ventas_detalles vd
            WHERE vd.uuid = :sale_detail_uuid
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->bindValue(':payment', $data['payment'], PDO::PARAM_INT);
        $stmt->bindValue(':sale_detail_uuid', $data['sale_detail'], PDO::PARAM_LOB);
        $stmt->bindValue(':balance_due_before', $data['balance_due_before'], PDO::PARAM_STR);
        $stmt->bindValue(':payment_amount', $data['payment_amount'], PDO::PARAM_STR);
        $stmt->bindValue(':balance_due', $data['balance_due'], PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        return $this->db->lastInsertId();
    }
}