<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\UsersRepository;
use InvalidArgumentException;
use RuntimeException;

class UsersService extends Service
{
    public function __construct(
        private UsersRepository $usersRepository
    ) {
    }

    public function getAll(?string $search = null, int $limit = 10, int $offset = 0, string $status = ''): array {
        try {
            $data = $this->usersRepository->getAll($search !== '' ? $search : null);
            $users = array();

            foreach($data as $d) {
                array_push($users, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'user'                      => $d['usuario'],
                    'email'                     => $d['email'],
                    'name'                      => $d['nombre'],
                    'type'                      => $d['tipo'],
                    'active'                    => $d['activo'] ?? 0,
                    'registered_date'           => $d['f_registro'] ?? '',
                    'last_active_date'          => $d['f_ultima_conexion'] ?? ''
                ));
            }

            return $users;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}