<?php

namespace App\Application\CQRS\Queries\Product;

use App\Application\DTOs\Inputs\ProductFiltersInput;
use App\Application\DTOs\Outputs\ProductOutput;
use App\Domain\Interfaces\ProductRepositoryInterface;

/**
 * Query para listar produtos com filtros dinâmicos
 */
readonly class GetAllProductsQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(ProductFiltersInput $filters): array
    {
        $products = $this->productRepository->getAllWithFilters($filters);

        return [
            'data' => $products->map(fn($product) => ProductOutput::fromModel($product)->toArray()),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ];
    }
}
