<?php

namespace App\Application\UseCases\Category;

use App\Application\CQRS\Queries\Category\GetAllCategoriesQuery;
use App\Application\Errors\CategoryNotFoundError;
use App\Application\Errors\DeleteCategoryError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\CategoryRepositoryInterface;

/**
 * UseCase para deletar categoria (soft delete)
 */
readonly class DeleteCategoryUseCase
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(string $uuid): bool
    {
        $category = $this->categoryRepository->findByUuid($uuid);
        
        if (!$category) {
            throw new EcommerceException(CategoryNotFoundError::class);
        }

        $deleted = $this->categoryRepository->delete($uuid);

        if(!$deleted) {
            throw new EcommerceException(DeleteCategoryError::class);
        }
        
        // Limpar cache de categorias
        GetAllCategoriesQuery::clearCache();
        
        return $deleted;
    }
}
