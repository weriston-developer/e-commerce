<?php

namespace App\Application\CQRS\Queries\Category;

use App\Application\DTOs\Outputs\CategoryOutput;
use App\Domain\Interfaces\CategoryRepositoryInterface;

/**
 * Query para listar categorias ativas
 */
readonly class GetActiveCategoriesQuery
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(): array
    {
        $categories = $this->categoryRepository->getActive();

        return $categories->map(fn($category) => CategoryOutput::fromModel($category)->toArray())->toArray();
    }
}
