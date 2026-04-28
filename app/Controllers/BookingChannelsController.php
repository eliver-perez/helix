<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Repositories\BookingChannelsRepository;
use Throwable;

class BookingChannelsController extends Controller
{
    public function index(Request $request, Response $response)
    {
        try {
            $database = new Database();
            $conn = $database->getConnection();

            $repository = new BookingChannelsRepository($conn);
            $booking_types = $repository->getAll();

            return $response->json([
                'status' => 'OK',
                'data' => [
                    'booking_types' => $booking_types
                ]
            ]);
        } catch (Throwable $e) {
            return $response->json([
                'status' => 'ERROR',
                'message' => 'No fue posible obtener los tipos de cita.'
            ], 500);
        }
    }
}