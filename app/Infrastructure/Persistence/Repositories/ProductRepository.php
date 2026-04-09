<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\DTOs\Inputs\ProductFiltersInput;
use App\Application\Errors\ProductNotFoundError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Implementação do Repository de Produtos usando Eloquent
 */
class ProductRepository implements ProductRepositoryInterface
{
    public function findByUuid(string $uuid): ?Product
    {
        return Product::with('category')->where('uuid', $uuid)->first();
    }

    public function findById(int $id): ?Product
    {
        return Product::with('category')->find($id);
    }

    public function findBySlug(string $slug): ?Product
    {
        return Product::with('category')->where('slug', $slug)->first();
    }

    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        $query = Product::where('slug', $slug);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('category')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllWithFilters(ProductFiltersInput $filters): LengthAwarePaginator
    {
        $query = Product::with('category')
            ->whereHas('category', function ($q) {
                $q->where('is_active', true);
            });

        // Filtro: Busca por nome ou descrição
        if ($filters->search) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'LIKE', "%{$filters->search}%")
                  ->orWhere('description', 'LIKE', "%{$filters->search}%");
            });
        }

        // Filtro: Categorias específicas (array de UUIDs)
        if ($filters->categoryUuids && count($filters->categoryUuids) > 0) {
            $query->whereHas('category', function ($q) use ($filters) {
                $q->whereIn('uuid', $filters->categoryUuids);
            });
        }

        // Filtro: Range de preço (convertendo para centavos)
        if ($filters->minPrice !== null) {
            $minCents = (int) ($filters->minPrice * 100);
            $query->where('price', '>=', $minCents);
        }

        if ($filters->maxPrice !== null) {
            $maxCents = (int) ($filters->maxPrice * 100);
            $query->where('price', '<=', $maxCents);
        }

        // Filtro: Apenas produtos ativos
        if ($filters->onlyActive) {
            $query->where('is_active', true);
        }

        // Filtro: Apenas produtos com estoque
        if ($filters->onlyInStock) {
            $query->where('stock', '>', 0);
        }

        // Ordenação
        $allowedSorts = ['created_at', 'name', 'price'];
        $sortBy = in_array($filters->sortBy, $allowedSorts) ? $filters->sortBy : 'created_at';
        $sortOrder = in_array(strtolower($filters->sortOrder), ['asc', 'desc']) ? $filters->sortOrder : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($filters->perPage);
    }

    public function getByCategoryId(string $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('category')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->where('category_id', $categoryId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('category')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->where('is_active', true)
            ->where('stock', '>=', 0)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getInStock(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('category')
            ->whereHas('category', function ($query) {
                $query->where('is_active', true);
            })
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getByPriceRange(int $minCents, int $maxCents, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('category')
            ->where('is_active', true)
            ->whereBetween('price', [$minCents, $maxCents])
            ->orderBy('price', 'asc')
            ->paginate($perPage);
    }

    public function search(string $term, int $perPage = 15): LengthAwarePaginator
    {
        return Product::with('category')
            ->where('is_active', true)
            ->where(function ($query) use ($term) {
                $query->where('name', 'LIKE', "%{$term}%")
                    ->orWhere('description', 'LIKE', "%{$term}%")
                    ->orWhere('sku', 'LIKE', "%{$term}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(string $uuid, array $data): Product
    {
        $product = $this->findByUuid($uuid);

        if (!$product) {
            throw new EcommerceException(ProductNotFoundError::class);
        }

        $product->update($data);
        return $product->fresh(['category']);
    }

    public function delete(string $uuid): bool
    {
        $product = $this->findByUuid($uuid);

        if (!$product) {
            return false;
        }

        return $product->delete();
    }

    public function updateStock(string $id, int $quantity): bool
    {
        $product = $this->findById($id);

        if (!$product) {
            return false;
        }

        $product->stock = $quantity;
        return $product->save();
    }
}
