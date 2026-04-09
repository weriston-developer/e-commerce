<?php

namespace App\Application\UseCases\Product;

use App\Application\Errors\DeleteProductError;
use App\Application\Errors\ProductNotFoundError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\ProductRepositoryInterface;

/**
 * UseCase para deletar produto (soft delete)
 */
readonly class DeleteProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(string $uuid): bool
    {
        $product = $this->productRepository->findByUuid($uuid);
        
        if (!$product) {
            throw new EcommerceException(ProductNotFoundError::class);
        }

        $deleted = $this->productRepository->delete($uuid);

        if (!$deleted) {
            throw new EcommerceException(DeleteProductError::class);
        }

        return $deleted;
    }
}
