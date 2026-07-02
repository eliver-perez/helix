<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class PaymentsMethodsRepository
{
    public function __construct(private PDO $db) {
    }

    public function getConnection() : PDO {
        return $this->db;
    }

    public function getPaymentMethodIdByCode(string $code): ?int {
        $stmt = $this->db->prepare("
            SELECT id
            FROM metodos_pago
            WHERE codigo = :codigo
            LIMIT 1");
        $stmt->bindValue(':codigo', $code, PDO::PARAM_STR);
        $stmt->execute();
        $id = $stmt->fetchColumn();

        return $id !== false ? (int) $id : null;
    }
}