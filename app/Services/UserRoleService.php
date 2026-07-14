<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\UserRoleRepository;
use App\Repositories\UsersRepository;
use InvalidArgumentException;
use RuntimeException;

class UserRoleService extends Service
{
    public function __construct(
        private UserRoleRepository $userRoleRepository,
        private UsersRepository $usersRepository
    ) {
    }

    public function getRoles(?string $search = null, int $limit = 10, int $offset = 0, string $status = ''): array {
        try {
            $data = $this->userRoleRepository->getRoles();
            $roles = array();

            foreach($data as $d) {
                array_push($roles, array(
                    'id'                        => $d['id'],
                    'role'                      => $d['permiso'],
                    'description'               => $d['descripcion'],
                    'registered_date'           => $d['f_registro'],
                ));
            }

            return $roles;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getAll(?string $search = null, int $limit = 10, int $offset = 0, string $status = ''): array {
        
    }

    public function getUserTypeRoles($id): array {
        try {
            $data = $this->userRoleRepository->getUserTypeRoles($id);
            $roles = array();

            foreach($data as $d) {
                array_push($roles, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'role'                      => $d['permiso'],
                    'description'               => $d['descripcion'],
                    'update_date'               => $d['f_actualizacion'],
                ));
            }

            return $roles;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getUserRoles(array $data): array {
        try {
            $user = $this->normalizeRequiredText(
                $data['user'] ?? null,
                'Error al recibir el usuario.'
            );

            $user_id = $this->usersRepository->getUserIdByUuid($this->uuidStringtoBinary($user));

            $data = $this->userRoleRepository->getUserRoles($user_id);
            $permissions = array();


            foreach($data as $d) {
                array_push($permissions, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'role'                      => $d['permiso'],
                    'description'               => $d['descripcion'],
                    'update_date'               => $d['f_actualizacion'],
                ));
            }

            return $permissions;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function addUserTypePermission(array $data): array {
        try {
            $user_type = $this->normalizeRequiredInt(
                $data['user_type'] ?? null,
                'Error al recibir el tipo de usuario.'
            );

            $permission = $this->normalizeRequiredText(
                $data['permission'] ?? null,
                'Error al recibir el permiso a asignar.'
            );

            $check = $this->userRoleRepository->checkUserTypePermission([
                'user_type'                     => $user_type,
                'permission'                    => $permission
            ]);

            if($check)
                throw new \RuntimeException('El permiso seleccionado ya se encuentra asignado.');

            $uuid = $this->generateUuidBinary();
            $data = $this->userRoleRepository->addUserTypePermission([
                'uuid'                          => $uuid,
                'user_type'                     => $user_type,
                'permission'                    => $permission,
            ]);

            return [
                'id' => $this->uuidBinarytoString($uuid)
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function addUserPermission(array $data): array {
        try {
            $user = $this->normalizeRequiredText(
                $data['user'] ?? null,
                'Error al recibir el usuario.'
            );

            if(!$this->is_uuid($user))
                throw new \RuntimeException("El identificador del usuario no es valido");

            $permission = $this->normalizeRequiredText(
                $data['permission'] ?? null,
                'Error al recibir el permiso a asignar.'
            );

            $check = $this->userRoleRepository->checkUserPermission([
                'user'                          => $user,
                'permission'                    => $permission
            ]);

            if($check)
                throw new \RuntimeException('El permiso seleccionado ya se encuentra asignado.');

            $user_id = $this->usersRepository->getUserIdByUuid($this->uuidStringtoBinary($user));

            $uuid = $this->generateUuidBinary();
            $data = $this->userRoleRepository->addUserPermission([
                'uuid'                          => $uuid,
                'user'                          => $user_id,
                'permission'                    => $permission,
            ]);

            return [
                'id' => $this->uuidBinarytoString($uuid)
            ];
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}