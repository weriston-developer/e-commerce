<?php

namespace App\Application\CQRS\Queries\Product;

use App\Application\DTOs\Outputs\ProductOutput;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Domain\ValueObjects\MoneyVO;

/**
 * Query para buscar produtos por faixa de preço
 * Recebe preços em float e converte para centavos
 */
readonly class SearchProductsByPriceQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(float $minPrice, float $maxPrice, int $perPage = 15): array
    {
        // Converte preços de float para centavos usando MoneyVO
        $minMoney = new MoneyVO($minPrice);
        $maxMoney = new MoneyVO($maxPrice);
        
        $minCents = (int) ($minMoney->toFloat() * 100);
        $maxCents = (int) ($maxMoney->toFloat() * 100);

        $products = $this->productRepository->getByPriceRange($minCents, $maxCents, $perPage);

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
