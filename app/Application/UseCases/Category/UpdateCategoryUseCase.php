<?php

namespace App\Application\UseCases\Category;

use App\Application\DTOs\Inputs\UpdateCategoryInput;
use App\Application\DTOs\Outputs\CategoryOutput;
use App\Application\Errors\CategoryNotFoundError;
use App\Application\Errors\CategorySlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Application\CQRS\Queries\Category\GetAllCategoriesQuery;
use App\Domain\Entities\CategoryEntity;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Str;

/**
 * UseCase para atualizar categoria
 */
readonly class UpdateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(string $uuid, UpdateCategoryInput $input): CategoryOutput
    {
        $category = $this->categoryRepository->findByUuid($uuid);
        
        if (!$category) {
            throw new EcommerceException(CategoryNotFoundError::class);
        }

        $categoryEntity = CategoryEntity::fromModel($category);

        if ($input->name !== null) {
            $newSlug = Str::slug($input->name);
            
            if ($newSlug !== $category->slug && $this->categoryRepository->slugExists($newSlug, $category->id)) {
                throw new EcommerceException(new CategorySlugAlreadyExistsError($newSlug));
            }
            
            $categoryEntity->setName($input->name);
        }

        if ($input->description !== null) {
            $categoryEntity->setDescription($input->description);
        }

        if ($input->isActive !== null) {
            $categoryEntity->setIsActive($input->isActive);
        }

        $updatedCategory = $this->categoryRepository->update($uuid, $categoryEntity->toArray());

        // Limpa o cache de categorias
        GetAllCategoriesQuery::clearCache();

        return CategoryOutput::fromModel($updatedCategory);
    }
}
