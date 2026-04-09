<?php

declare(strict_types=1);

namespace App\Application\DTOs\Inputs;

/**
 * DTO para filtros de listagem de produtos
 */
readonly class ProductFiltersInput
{
    public function __construct(
        public ?string $search = null,           // Busca por nome/descrição
        public ?array $categoryUuids = null,     // Filtrar por UUIDs de categorias
        public ?float $minPrice = null,          // Preço mínimo
        public ?float $maxPrice = null,          // Preço máximo
        public ?bool $onlyActive = null,         // Apenas produtos ativos
        public ?bool $onlyInStock = null,        // Apenas com estoque
        public ?string $sortBy = 'created_at',   // Campo para ordenação
        public ?string $sortOrder = 'desc',      // Ordem: asc ou desc
        public int $perPage = 15,                // Itens por página
    ) {}

    /**
     * Cria a partir de array (request)
     */
    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            categoryUuids: isset($data['categories']) ? (array) $data['categories'] : null,
            minPrice: isset($data['min_price']) ? (float) $data['min_price'] : null,
            maxPrice: isset($data['max_price']) ? (float) $data['max_price'] : null,
            onlyActive: isset($data['only_active']) ? (bool) $data['only_active'] : null,
            onlyInStock: isset($data['only_in_stock']) ? (bool) $data['only_in_stock'] : null,
            sortBy: $data['sort_by'] ?? 'created_at',
            sortOrder: $data['sort_order'] ?? 'desc',
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 15,
        );
    }
}
