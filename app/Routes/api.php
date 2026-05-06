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
use App\Controllers\AppointmentsController;
use App\Controllers\GenderController;
use App\Controllers\ConsentTemplatesController;
use App\Controllers\AppointmentsTypesController;
use App\Controllers\AppointmentsStatusController;
use App\Controllers\BookingChannelsController;
use App\Controllers\ProceduresController;
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
 * APPOINTMENTS ROUTES
 */
$router->get('/api/appointments/calendar', [AppointmentsController::class, 'calendar']);
$router->get('/api/appointments/status', [AppointmentsStatusController::class, 'index']);
$router->post('/api/appointments', [AppointmentsController::class, 'schedule']);
$router->post('/api/appointments/available-slots', [AppointmentsController::class, 'availableSlots']);

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
 * CONSENT TEMPLATES ROUTES
 */
$router->get('/api/consent-templates', [ConsentTemplatesController::class, 'index']);
$router->post('/api/consent-templates', [ConsentTemplatesController::class, 'store']);
$router->get('/api/consent-templates/{id}', [ConsentTemplatesController::class, 'show']);
$router->put('/api/consent-templates/{id}', [ConsentTemplatesController::class, 'update']);

$router->post('/api/consent-templates/preview', [ConsentTemplatesController::class, 'preview']);

$router->get('/api/consent-templates/{id}/status', [ConsentTemplatesController::class, 'status']);
$router->put('/api/consent-templates/{id}/activate', [ConsentTemplatesController::class, 'activate']);

/**
 * APPOINTMENTS TYPE ROUTES
 */
$router->get('/api/appointments-types', [AppointmentsTypesController::class, 'index']);

/**
 * BOOKING TYPE ROUTES
 */
$router->get('/api/booking-channels', [BookingChannelsController::class, 'index']);

/**
 * PROCEDURES ROUTES
 */
$router->get('/api/procedures', [ProceduresController::class, 'index']);
$router->get('/api/procedures/{id}', [ProceduresController::class, 'show']);
$router->get('/api/procedures/{id}/staff', [ProceduresController::class, 'staff']);
$router->get('/api/procedures/{procedureId}/staff/{staffId}', [ProceduresController::class, 'procedureStaffData']);

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