<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\DiagnosticsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class DiagnosticsService extends Service
{
    public function __construct(
        private DiagnosticsRepository $diagnosticsRepository
    ) {
    }

    public function getDiagnostics(): array {
        try {
            $data = $this->diagnosticsRepository->getDiagnostics();
            $diagnostics = array();

            foreach($data as $d) {
                array_push($diagnostics, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'code'                      => $d['codigo'],
                    'diagnostic'                => $d['diagnostico'],
                    'category_code'             => $d['categoria_codigo'],
                    'category'                  => $d['categoria'],
                    'specialty_code'            => $d['especialidad_codigo'],
                    'specialty'                 => $d['especialidad'],
                ));
            }

            return $diagnostics;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getDiagnosticTypes(): array {
        try {
            $data = $this->diagnosticsRepository->getDiagnosticTypes();
            $diagnostic_types = array();
            foreach($data as $d) {
                array_push($diagnostic_types, array(
                    'id'                        => $d['id'],
                    'code'                      => $d['codigo'],
                    'type'                      => $d['tipo'],
                ));
            }

            return $diagnostic_types;
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}