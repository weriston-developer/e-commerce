<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Errors\CategoryNotFoundError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Implementação do Repository de Categorias usando Eloquent
 */
class CategoryRepository implements CategoryRepositoryInterface
{
    public function findByUuid(string $uuid): ?Category
    {
        return Category::where('uuid', $uuid)
            ->first();
    }

    public function findById(string $id): ?Category
    {
        return Category::find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->where('is_active', true)->first();
    }

    public function getAll(): Collection
    {
        return Category::orderBy('name', 'asc')->where('is_active', true)->get();
    }

    public function getActive(): Collection
    {
        return Category::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(string $uuid, array $data): Category
    {
        $category = $this->findByUuid($uuid);

        if (!$category) {
            throw new EcommerceException(CategoryNotFoundError::class);
        }

        $category->update($data);
        return $category->fresh();
    }

    public function delete(string $uuid): bool
    {
        $category = $this->findByUuid($uuid);

        if (!$category) {
            return false;
        }

        return $category->delete();
    }

    public function slugExists(string $slug, ?string $excludeId = null): bool
    {
        $query = Category::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
