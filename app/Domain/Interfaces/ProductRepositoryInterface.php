<?php

namespace App\Domain\Interfaces;

use App\Infrastructure\Persistence\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface para Repository de Produtos
 */
interface ProductRepositoryInterface
{
    public function findByUuid(string $uuid): ?Product;
    /**
     * Busca produto por ID
     */
    public function findById(int $id): ?Product;

    /**
     * Busca produto por slug
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Verifica se existe produto com o slug
     */
    public function existsBySlug(string $slug, ?int $excludeId = null): bool;

    /**
     * Lista todos os produtos com paginação
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    /**
     * Lista produtos com filtros dinâmicos
     */
    public function getAllWithFilters(\App\Application\DTOs\Inputs\ProductFiltersInput $filters): LengthAwarePaginator;

    /**
     * Lista produtos por categoria
     */
    public function getByCategoryId(string $categoryId, int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca produtos ativos
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca produtos em estoque
     */
    public function getInStock(int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca produtos por range de preço
     */
    public function getByPriceRange(int $minCents, int $maxCents, int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca produtos por termo
     */
    public function search(string $term, int $perPage = 15): LengthAwarePaginator;

    /**
     * Cria novo produto
     */
    public function create(array $data): Product;

    /**
     * Atualiza produto
     */
    public function update(string $id, array $data): Product;

    /**
     * Deleta produto (soft delete)
     */
    public function delete(string $id): bool;

    /**
     * Atualiza estoque
     */
    public function updateStock(string $id, int $quantity): bool;
}
