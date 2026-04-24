<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function id(): ?int
    {
        return $_SESSION['HELIX_ERP_ID'] ?? null;
    }
}