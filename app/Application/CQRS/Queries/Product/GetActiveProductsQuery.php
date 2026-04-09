<?php

namespace App\Application\CQRS\Queries\Product;

use App\Application\DTOs\Outputs\ProductOutput;
use App\Domain\Interfaces\ProductRepositoryInterface;

/**
 * Query para buscar produtos ativos
 */
readonly class GetActiveProductsQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $perPage = 15): array
    {
        $products = $this->productRepository->getActive($perPage);

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
