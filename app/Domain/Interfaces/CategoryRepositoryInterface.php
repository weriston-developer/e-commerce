<?php

namespace App\Domain\Interfaces;

use App\Infrastructure\Persistence\Models\Category;
use Illuminate\Database\Eloquent\Collection;

/**
 * Interface para Repository de Categorias
 */
interface CategoryRepositoryInterface
{
    /**
     * Busca categoria por UUID
     */
    public function findByUuid(string $uuid): ?Category;

    /**
     * Busca categoria por ID
     */
    public function findById(string $id): ?Category;

    /**
     * Busca categoria por slug
     */
    public function findBySlug(string $slug): ?Category;

    /**
     * Lista todas as categorias
     */
    public function getAll(): Collection;

    /**
     * Lista categorias ativas
     */
    public function getActive(): Collection;

    /**
     * Cria nova categoria
     */
    public function create(array $data): Category;

    /**
     * Atualiza categoria
     */
    public function update(string $id, array $data): Category;

    /**
     * Deleta categoria (soft delete)
     */
    public function delete(string $id): bool;

    /**
     * Verifica se slug já existe
     */
    public function slugExists(string $slug, ?string $excludeId = null): bool;
}
