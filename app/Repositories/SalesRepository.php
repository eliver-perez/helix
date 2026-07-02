<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class SalesRepository
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

    public function createFromConsultation(array $data) {
        $sql = "
            INSERT INTO ventas (
                uuid,
                folio,
                consecutivo,
                consulta,
                cita,
                cliente,
                paciente,
                subtotal,
                impuestos,
                total,
                descuento,
                pagado,
                adeudo,
                estatus,
                observaciones,
                registro,
                f_venta,
                f_registro
            )
            SELECT 
                :uuid,
                :folio,
                :consecutive,
                c.id,
                c.cita,
                cp.cliente,
                c.paciente,
                :subtotal,
                0,
                :total,
                :discount,
                0,
                :debt,
                :status,
                :observations,
                :uid,
                NOW(),
                NOW()
            FROM consultas c
                INNER JOIN pacientes p
                    ON c.paciente = p.id
                INNER JOIN clientes_pacientes cp
                    ON cp.paciente = p.id
                        AND cp.principal = 1
                        AND cp.activo = 1
            WHERE c.uuid = :consultation_uuid
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->bindValue(':folio', $data['folio'], PDO::PARAM_STR);
        $stmt->bindValue(':consecutive', $data['consecutive'], PDO::PARAM_INT);
        $stmt->bindValue(':subtotal', $data['total'], PDO::PARAM_STR);
        $stmt->bindValue(':total', $data['total'], PDO::PARAM_STR);
        $stmt->bindValue(':discount', $data['discount'], PDO::PARAM_STR);
        $stmt->bindValue(':debt', $data['debt'], PDO::PARAM_STR);
        $stmt->bindValue(':status', $data['status'], PDO::PARAM_INT);
        $stmt->bindValue(':observations', $data['observations'], PDO::PARAM_STR);
        $stmt->bindValue(':uid', $data['uid'], PDO::PARAM_INT);
        $stmt->bindValue(':consultation_uuid', $data['consultation_uuid'], PDO::PARAM_LOB);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        return $this->db->lastInsertId();
    }

    public function createFromConsultationDetails(array $data) {
        $sql = "
            INSERT INTO ventas_detalles (
                uuid,
                venta,
                servicio,
                descripcion,
                cantidad,
                precio_base,
                subtotal,
                impuestos,
                total,
                descuento,
                adeudo,
                f_registro
            ) VALUES (
                :uuid,
                :sale,
                :procedure,
                :description,
                :quantity,
                :unit_price,
                :subtotal,
                0,
                :total,
                :discount,
                :debt,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':uuid', $data['uuid'], PDO::PARAM_LOB);
        $stmt->bindValue(':sale', $data['sale'], PDO::PARAM_INT);
        $stmt->bindValue(':procedure', $data['procedure'], PDO::PARAM_INT);
        $stmt->bindValue(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindValue(':quantity', $data['quantity'], PDO::PARAM_STR);
        $stmt->bindValue(':unit_price', $data['unit_price'], PDO::PARAM_STR);
        $stmt->bindValue(':subtotal', $data['total'], PDO::PARAM_STR);
        $stmt->bindValue(':total', $data['total'], PDO::PARAM_STR);
        $stmt->bindValue(':discount', $data['discount'], PDO::PARAM_STR);
        $stmt->bindValue(':debt', $data['debt'], PDO::PARAM_STR);

        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return false;
        }

        return $this->db->lastInsertId();
    }

    public function getSaleData($uuid) {
        $stmt = $this->db->prepare("
            SELECT v.id,
                v.uuid,
                v.folio,
                cl.uuid cliente_uuid,
                TRIM(
                    CONCAT(
                        cl.nombre, ' ',
                        COALESCE(cl.paterno, ''), ' ',
                        COALESCE(cl.materno, '')
                    )
                ) cliente,
                p.uuid paciente_uuid,
                TRIM(
                    CONCAT(
                        p.nombre, ' ',
                        COALESCE(p.paterno, ''), ' ',
                        COALESCE(p.materno, '')
                    )
                ) paciente,
                v.subtotal,
                v.impuestos,
                v.total,
                v.descuento,
                v.pagado,
                v.adeudo,
                v.observaciones
            FROM ventas v
                LEFT JOIN clientes cl
                    ON v.cliente = cl.id
                LEFT JOIN pacientes p
                    ON v.paciente = p.id
                INNER JOIN usuarios u
                    ON v.registro = u.id
            WHERE v.uuid = :uuid
            LIMIT 1");
        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();
        $row = $stmt->fetch();

        return $row !== false ? $row : null;
    }

    public function getSaleDetails($uuid) {
        $stmt = $this->db->prepare("
            SELECT vd.id,
                vd.uuid,
                s.id servicio_id,
                s.uuid servicio_uuid,
                s.codigo servicio_codigo,
                s.servicio,
                p.id producto_id,
                p.uuid producto_uuid,
                p.clave producto_codigo,
                p.nombre producto,
                vd.descripcion,
                vd.cantidad,
                vd.precio_base,
                vd.subtotal,
                vd.impuestos,
                vd.total,
                vd.descuento,
                vd.pagado,
                vd.adeudo
            FROM ventas v
                INNER JOIN ventas_detalles vd
                    ON vd.venta = v.id
                LEFT JOIN servicios s
                    ON vd.servicio = s.id
                LEFT JOIN productos p
                    ON vd.producto = p.id
            WHERE v.uuid = :uuid
        ");

        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSaleBalanceDue($uuid): float {
        $stmt = $this->db->prepare("
            SELECT 
                v.adeudo
            FROM ventas v
            WHERE v.uuid = :uuid
        ");

        $stmt->bindValue(':uuid', $uuid, PDO::PARAM_LOB);
        $stmt->execute();

        $balance_due = $stmt->fetchColumn();

        return $balance_due != null ? (float) $balance_due : 0;
    }
    
    public function updateSaleBalance(array $data) {
        try {
            $sql = "
                UPDATE ventas SET
                    pagado = pagado + :payment_amount,
                    adeudo = :balance_due,
                    estatus = :status
                WHERE uuid = :sale
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':payment_amount', $data['payment_amount'], PDO::PARAM_STR);
            $stmt->bindValue(':balance_due', $data['balance_due'], PDO::PARAM_STR);
            $stmt->bindValue(':status', $data['status'], PDO::PARAM_INT);
            $stmt->bindValue(':sale', $data['sale'], PDO::PARAM_LOB);

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch(Exception $ex) {
            throw $ex;
        }
    }
    
    public function updateSaleDetailBalance(array $data) {
        try {
            $sql = "
                UPDATE ventas_detalles SET
                    pagado = pagado + :payment_amount,
                    adeudo = :balance_due
                WHERE uuid = :sale
            ";

            $stmt = $this->db->prepare($sql);

            $stmt->bindValue(':payment_amount', $data['payment_amount'], PDO::PARAM_STR);
            $stmt->bindValue(':balance_due', $data['balance_due'], PDO::PARAM_STR);
            $stmt->bindValue(':sale', $data['sale'], PDO::PARAM_LOB);

            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return false;
            }

            return true;
        } catch(Exception $ex) {
            throw $ex;
        }
    }
}