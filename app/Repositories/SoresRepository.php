<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class SoresRepository
{
    public function __construct(private PDO $db)
    {
    }
}