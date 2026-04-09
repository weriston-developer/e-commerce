<?php

namespace App\Application\DTOs\Inputs;

use App\Domain\ValueObjects\MoneyVO;

/**
 * DTO para criação de produto
 * 
 * Recebe price já como MoneyVO (controller converte)
 */
readonly class CreateProductInput
{
    public function __construct(
        public string $name,
        public string $categoryId,
        public MoneyVO $price,
        public ?string $description = null,
        public ?string $imageUrl = null,
        public int $stock = 0,
        public ?string $sku = null,
        public bool $isActive = true,
    ) {}

    /**
     * Cria a partir de array - Controller usa este método
     * Converte float do request para MoneyVO
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            categoryId: $data['category_uuid'], // Ajustado para category_uuid
            price: new MoneyVO($data['price']), // Converte float para MoneyVO
            description: $data['description'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            stock: (int) ($data['stock'] ?? 0),
            sku: $data['sku'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    /**
     * Converte para array para salvar no banco
     * Price já é MoneyVO, converte para centavos (integer)
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'price' => (int) ($this->price->toFloat() * 100), // MoneyVO para centavos
            'description' => $this->description,
            'image_url' => $this->imageUrl,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'is_active' => $this->isActive,
        ];
    }
}
