<?php

namespace App\Application\CQRS\Queries\Category;

use App\Application\DTOs\Outputs\CategoryOutput;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Cache;

/**
 * Query para listar todas as categorias
 */
readonly class GetAllCategoriesQuery
{
    private const CACHE_KEY = 'categories:all';

    public function __construct(
        private CategoryRepositoryInterface $categoryRepository
    ) {}

    public function execute(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $categories = $this->categoryRepository->getAll();

            return $categories->map(fn($category) => CategoryOutput::fromModel($category)->toArray())->toArray();
        });
    }

    /**
     * Limpa o cache de categorias
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
