<?php

namespace App\Application\DTOs\Outputs;

use App\Infrastructure\Persistence\Models\Category;

/**
 * DTO para retornar dados da categoria
 */
readonly class CategoryOutput
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $slug,
        public ?string $description,
        public bool $isActive,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Category $category): self
    {
        return new self(
            uuid: $category->uuid,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            isActive: $category->is_active,
            createdAt: $category->created_at->toIso8601String(),
            updatedAt: $category->updated_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
