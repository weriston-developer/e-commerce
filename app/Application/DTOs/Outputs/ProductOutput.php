<?php

namespace App\Application\DTOs\Outputs;

use App\Infrastructure\Persistence\Models\Product;
use App\Domain\ValueObjects\MoneyVO;

/**
 * DTO para retornar dados do produto
 * 
 * Converte price de centavos (int) para float usando MoneyVO
 */
readonly class ProductOutput
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $slug,
        public ?string $description,
        public float $price,
        public string $priceFormatted,
        public string $categoryUuid,
        public ?CategoryOutput $category,
        public ?string $imageUrl,
        public bool $isAvailable,
        public int $stock,
        public string $createdAt,
        public string $updatedAt,
    ) {}

    public static function fromModel(Product $product): self
    {
        $money = new MoneyVO($product->price / 100);

        return new self(
            uuid: $product->uuid,
            name: $product->name,
            slug: $product->slug,
            description: $product->description,
            price: $money->toFloat(),
            priceFormatted: $money->format(),
            categoryUuid: $product->category->uuid,
            category: $product->category ? CategoryOutput::fromModel($product->category) : null,
            imageUrl: $product->image_url,
            isAvailable: $product->isAvailable(),
            stock: $product->stock,
            createdAt: $product->created_at->toIso8601String(),
            updatedAt: $product->updated_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'price_formatted' => $this->priceFormatted,
            'category' => $this->category?->toArray(),
            'image_url' => $this->imageUrl,
            'is_available' => $this->isAvailable,
            'stock' => $this->stock,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
