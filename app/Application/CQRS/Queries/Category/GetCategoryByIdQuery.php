<?php

namespace App\Application\CQRS\Queries\Category;

use App\Application\DTOs\Outputs\CategoryOutput;
use App\Domain\Interfaces\CategoryRepositoryInterface;

/**
 * Query para buscar categoria por UUID
 */
readonly class GetCategoryByIdQuery
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(string $uuid): ?CategoryOutput
    {
        $category = $this->categoryRepository->findByUuid($uuid);

        if (!$category) {
            return null;
        }

        return CategoryOutput::fromModel($category);
    }
}
