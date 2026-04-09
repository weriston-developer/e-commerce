<?php

namespace App\Application\CQRS\Queries\Product;

use App\Application\DTOs\Outputs\ProductOutput;
use App\Application\Errors\CategoryNotFoundError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Domain\Interfaces\ProductRepositoryInterface;

/**
 * Query para buscar produtos por UUID de categoria
 */
readonly class GetProductsByCategoryQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(string $categoryUuid, int $perPage = 15): array
    {
        $category = $this->categoryRepository->findByUuid($categoryUuid);

        if (!$category) {
            throw new EcommerceException(CategoryNotFoundError::class);
        }

        $products = $this->productRepository->getByCategoryId($category->id, $perPage);

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
