<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Controllers\AuthController;
use App\Controllers\StaffController;
use App\Controllers\UsersController;
use App\Controllers\BillingController;
use App\Controllers\PatientsController;
use App\Controllers\GenderController;
use App\Controllers\UserRoleController;
use App\Controllers\RoleController;
use App\Controllers\SpecialtyController;
use App\Controllers\LocationController;
use App\Core\Response;

$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);

/**
 * STAFF ROUTES
 */
$router->get('/api/staff', [StaffController::class, 'index']);
$router->get('/api/staff/{id}', [StaffController::class, 'show']);
$router->post('/api/staff', [StaffController::class, 'store']);

/**
 * USERS ROUTES
 */
$router->get('/api/users', [UsersController::class, 'index']);

/**
 * BILLING ROUTES
 */
$router->get('/api/billing-regimenes', [BillingController::class, 'regimenes']);

/**
 * PATIENTS ROUTES
 */
$router->get('/api/patients', [PatientsController::class, 'index']);
$router->post('/api/patients', [PatientsController::class, 'store']);

/**
 * GENDER ROUTES
 */
$router->get('/api/genders', [GenderController::class, 'index']);

/**
 * USER ROLE ROUTES
 */
$router->get('/api/users-roles', [UserRoleController::class, 'index']);

/**
 * STAFF ROLE ROUTES
 */
$router->get('/api/roles', [RoleController::class, 'index']);

/**
 * SPECIALTIES ROUTES
 */
$router->get('/api/specialties', [SpecialtyController::class, 'index']);

/**
 * LOCATION ROUTES
 */
$router->get('/api/locations/countries', [LocationController::class, 'countries']);
$router->get('/api/locations/states', [LocationController::class, 'states']);
$router->get('/api/locations/municipalities', [LocationController::class, 'municipalities']);
$router->get('/api/locations/localities', [LocationController::class, 'localities']);

$router->post('/api/locations/states', [LocationController::class, 'storeState']);
$router->post('/api/locations/municipalities', [LocationController::class, 'storeMunicipality']);
$router->post('/api/locations/localities', [LocationController::class, 'storeLocality']);