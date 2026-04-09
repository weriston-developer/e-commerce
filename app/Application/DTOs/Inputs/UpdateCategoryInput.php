<?php

namespace App\Application\DTOs\Inputs;

/**
 * DTO para atualização de categoria
 */
readonly class UpdateCategoryInput
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?bool $isActive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->description !== null) $data['description'] = $this->description;
        if ($this->isActive !== null) $data['is_active'] = $this->isActive;

        return $data;
    }
}
