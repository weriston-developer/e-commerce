<?php

namespace App\Application\DTOs\Inputs;

/**
 * DTO para criação de categoria
 */
readonly class CreateCategoryInput
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }
}
