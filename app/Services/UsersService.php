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
}