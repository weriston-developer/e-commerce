<?php

namespace App\Application\UseCases\Product;

use App\Application\DTOs\Inputs\UpdateProductInput;
use App\Application\DTOs\Outputs\ProductOutput;
use App\Application\Errors\CategoryNotFoundError;
use App\Application\Errors\ProductNotFoundError;
use App\Application\Errors\ProductSlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Entities\ProductEntity;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Domain\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Str;

/**
 * UseCase para atualizar produto
 */
readonly class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(string $uuid, UpdateProductInput $input): ProductOutput
    {
        $product = $this->productRepository->findByUuid($uuid);
        
        if (!$product) {
            throw new EcommerceException(ProductNotFoundError::class);
        }

        $productEntity = ProductEntity::fromModel($product);

        if ($input->name !== null) {
            $newSlug = Str::slug($input->name);
            
            if ($newSlug !== $product->slug && $this->productRepository->existsBySlug($newSlug, $product->id)) {
                throw new EcommerceException(new ProductSlugAlreadyExistsError($newSlug));
            }
            
            $productEntity->setName($input->name);
        }

        if ($input->price !== null) {
            $productEntity->setPrice($input->price);
        }

        if ($input->description !== null) {
            $productEntity->setDescription($input->description);
        }

        if ($input->imageUrl !== null) {
            $productEntity->setImageUrl($input->imageUrl);
        }

        if ($input->categoryUuid !== null) {
            $category = $this->categoryRepository->findByUuid($input->categoryUuid);
            
            if (!$category) {
                throw new EcommerceException(CategoryNotFoundError::class);
            }
            
            $productEntity->setCategoryId($category->id);
        }

        if ($input->stock !== null) {
            $productEntity->setStock($input->stock);
        }

        if ($input->isActive !== null) {
            $productEntity->setIsActive($input->isActive);
        }

        $updatedProduct = $this->productRepository->update($uuid, $productEntity->toArray());

        return ProductOutput::fromModel($updatedProduct);
    }
}
