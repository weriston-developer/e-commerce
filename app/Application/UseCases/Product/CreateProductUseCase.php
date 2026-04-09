<?php

namespace App\Application\UseCases\Product;

use App\Application\DTOs\Inputs\CreateProductInput;
use App\Application\DTOs\Outputs\ProductOutput;
use App\Application\Errors\CategoryNotExistsError;
use App\Application\Errors\ProductSlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Entities\ProductEntity;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Domain\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Str;

/**
 * UseCase para criar produto
 */
readonly class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(CreateProductInput $input): ProductOutput
    {
        $category = $this->categoryRepository->findByUuid($input->categoryId);
        
        if (!$category) {
            throw new EcommerceException(new CategoryNotExistsError($input->categoryId));
        }

        $slug = Str::slug($input->name);

        if ($this->productRepository->existsBySlug($slug)) {
            throw new EcommerceException(new ProductSlugAlreadyExistsError($slug));
        }

        $productEntity = ProductEntity::fromArray([
            'name' => $input->name,
            'slug' => $slug,
            'price' => $input->price,
            'category_id' => $category->id,
            'description' => $input->description,
            'image_url' => $input->imageUrl,
            'stock' => $input->stock,
            'sku' => $input->sku,
            'is_active' => $input->isActive,
        ]);

        $product = $this->productRepository->create($productEntity->toArray());

        return ProductOutput::fromModel($product);
    }
}
