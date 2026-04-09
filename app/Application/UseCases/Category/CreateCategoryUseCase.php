<?php

namespace App\Application\UseCases\Category;

use App\Application\DTOs\Inputs\CreateCategoryInput;
use App\Application\DTOs\Outputs\CategoryOutput;
use App\Application\Errors\CategorySlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Application\CQRS\Queries\Category\GetAllCategoriesQuery;
use App\Domain\Entities\CategoryEntity;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Str;

/**
 * UseCase para criar categoria
 */
readonly class CreateCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(CreateCategoryInput $input): CategoryOutput
    {
        $slug = Str::slug($input->name);

        if ($this->categoryRepository->slugExists($slug)) {
            throw new EcommerceException(new CategorySlugAlreadyExistsError($slug));
        }

        $categoryEntity = CategoryEntity::fromArray([
            'name' => $input->name,
            'slug' => $slug,
            'description' => $input->description,
            'is_active' => $input->isActive ?? true,
        ]);

        $category = $this->categoryRepository->create($categoryEntity->toArray());

        GetAllCategoriesQuery::clearCache();

        return CategoryOutput::fromModel($category);
    }
}
