<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Service;
use App\Repositories\ProductsRepository;
use App\Services\SettingsService;
use InvalidArgumentException;
use RuntimeException;

class ProductsService extends Service
{
    public function __construct(
        private ProductsRepository $productsRepository
    ) {
    }

    public function getAll($search, $limit, $offset): array {
        try {
            $data = $this->productsRepository->getAll($search, $limit, $offset);
            $procedures = array();

            foreach($data as $d) {
                array_push($procedures, array(
                    'id'                        => $this->uuidBinaryToString($d['uuid']),
                    'code'                      => $d['clave'],
                    'product'                   => $d['nombre'],
                    'category'                  => $d['categoria'],
                    'unit_measure'              => $d['unidad'],
                    'total_cost'                => $d['precio_total'],
                    'enabled_sale'              => $d['habilitado_venta'],
                ));
            }

            return $procedures;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function getCategories(): array {
        try {
            $data = $this->productsRepository->getCategories();
            $categories = array();

            foreach($data as $d) {
                array_push($categories, array(
                    'id'                        => $d['id'],
                    'category'                  => $d['categoria'],
                ));
            }

            return $categories;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    public function create(array $data): ?string {
        $uid = $this->normalizeRequiredInt($data['uid'] ?? null, 'No existe una sesion activa.');
        $code = $this->normalizeOptionalText(trim($data['code'] ?? ''));
        $bar_code = $this->normalizeOptionalText(trim($data['bar_code'] ?? ''));
        $category = $this->normalizeRequiredInt($data['category'] ?? null, 'Es necesario seleccionar una categoria.');
        $product = $this->normalizeRequiredText($data['product'] ?? null, 'Es necesario capturar el nombre del producto.');
        $description = $this->normalizeOptionalText(trim($data['description'] ?? ''));
        $unit_measure = $this->normalizeRequiredText($data['unit_measure'] ?? null, 'Es necesario seleccionar la unidad de medida.');
        $base_cost = $this->normalizeRequiredFloat($data['base_cost'] ?? null, 'Es necesario capturar el costo base.');
        $tax_rate = $this->normalizeRequiredFloat($data['tax_rate'] ?? null, 'Es necesario capturar el porcentaje de impuestos.', true);
        $taxes = $this->normalizeRequiredFloat($data['taxes'] ?? null, 'Es necesario capturar el porcentaje de impuestos.', true);
        $total_cost = $this->normalizeRequiredFloat($data['total_cost'] ?? null, 'Es necesario capturar el precio del producto.');
        $enable_sale = $this->normalizeRequiredInt($data['enable_sale'] ?? null, 'Es necesario seleccionar si se habilita para venta.');

        $conn = $this->productsRepository->getConnection();
        $conn->beginTransaction();
        try {
            $productUuid = $this->generateUuidBinary();
            $productId = $this->productsRepository->insert([
                'uuid'                          => $productUuid,
                'code'                          => $code,
                'bar_code'                      => $bar_code,
                'product'                       => $product,
                'product_short'                 => strlen($product) > 32 ? substr($product, 0, 32) : $product,
                'category'                      => $category,
                'description'                   => $description,
                'unit_measure'                  => $unit_measure,
                'base_cost'                     => $base_cost,
                'tax_rate'                      => $tax_rate,
                'taxes'                         => $taxes,
                'total_cost'                    => $total_cost,
                'enable_sale'                   => $enable_sale,
                'uid'                           => $uid,
            ]);
            
            $productCostUuid = $this->generateUuidBinary();
            $productCost = $this->productsRepository->insertCost([
                'uuid'                          => $productCostUuid,
                'product'                       => $productId,
                'tax_profile'                   => 1,
                'base_cost'                     => $base_cost,
                'tax_rate'                      => $tax_rate,
                'taxes'                         => $taxes,
                'total_cost'                    => $total_cost,
                'uid'                           => $uid
            ]);
            
            $conn->commit();
            
            return $this->uuidBinaryToString($productUuid);
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw $e;
        }
    }
}