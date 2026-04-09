<?php

namespace App\Application\DTOs\Inputs;

use App\Domain\ValueObjects\MoneyVO;

/**
 * DTO para atualização de produto
 * 
 * Recebe price já como MoneyVO quando fornecido
 */
readonly class UpdateProductInput
{
    public function __construct(
        public ?string $name = null,
        public ?string $categoryUuid = null,
        public ?MoneyVO $price = null,
        public ?string $description = null,
        public ?string $imageUrl = null,
        public ?int $stock = null,
        public ?bool $isActive = null,
    ) {}

    /**
     * Cria a partir de array - Controller usa este método
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            categoryUuid: $data['category_uuid'] ?? null,
            price: isset($data['price']) ? new MoneyVO($data['price']) : null,
            description: $data['description'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            stock: isset($data['stock']) ? (int) $data['stock'] : null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : null,
        );
    }

    /**
     * Converte apenas os campos preenchidos
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) $data['name'] = $this->name;
        if ($this->categoryUuid !== null) $data['category_uuid'] = $this->categoryUuid;
        if ($this->description !== null) $data['description'] = $this->description;
        if ($this->imageUrl !== null) $data['image_url'] = $this->imageUrl;
        if ($this->stock !== null) $data['stock'] = $this->stock;
        if ($this->isActive !== null) $data['is_active'] = $this->isActive;

        // Converte MoneyVO para centavos se fornecido
        if ($this->price !== null) {
            $data['price'] = (int) ($this->price->toFloat() * 100);
        }

        return $data;
    }
}
