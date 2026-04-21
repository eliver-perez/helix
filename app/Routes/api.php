<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\StaffController;
use App\Core\Response;

$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);

$router->get('/api/staff', [StaffController::class, 'index']);
$router->get('/api/staff/{id}', [StaffController::class, 'show']);