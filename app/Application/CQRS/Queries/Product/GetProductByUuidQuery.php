<?php

namespace App\Application\CQRS\Queries\Product;

use App\Application\DTOs\Outputs\ProductOutput;
use App\Domain\Interfaces\ProductRepositoryInterface;

/**
 * Query para buscar produto por UUID
 */
readonly class GetProductByUuidQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(string $uuid): ?ProductOutput
    {
        $product = $this->productRepository->findByUuid($uuid);

        if (!$product) {
            return null;
        }

        return ProductOutput::fromModel($product);
    }
}
