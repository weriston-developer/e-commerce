<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\ValueObjects\MoneyVO;
use App\Infrastructure\Persistence\Models\Product;

/**
 * Entidade de Produto
 * Contém as regras de negócio do domínio
 */
class ProductEntity
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public string $name,
        public string $slug,
        public MoneyVO $price,
        public ?int $categoryId,
        public ?string $description = null,
        public ?string $imageUrl = null,
        public int $stock = 0,
        public ?string $sku = null,
        public bool $isActive = true,
    ) {
        $this->validate();
    }

    /**
     * Validações de regras de negócio
     */
    private function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('O nome do produto é obrigatório');
        }

        if (strlen($this->name) > 200) {
            throw new \InvalidArgumentException('O nome do produto não pode ter mais de 200 caracteres');
        }

        if ($this->price->toFloat() < 0) {
            throw new \InvalidArgumentException('O preço não pode ser negativo');
        }

        if ($this->stock < 0) {
            throw new \InvalidArgumentException('O estoque não pode ser negativo');
        }
    }

    /**
     * Cria entidade a partir do Model (vindo do banco)
     * Converte campos e VOs automaticamente
     */
    public static function fromModel(Product $model): self
    {
        return new self(
            id: $model->id,
            uuid: $model->uuid,
            name: $model->name,
            slug: $model->slug,
            price: new MoneyVO($model->price / 100), // De centavos para decimal
            categoryId: $model->category_id,
            description: $model->description,
            imageUrl: $model->image_url,
            stock: $model->stock,
            sku: $model->sku,
            isActive: $model->is_active,
        );
    }

    /**
     * Cria entidade a partir de array (dados estruturados do sistema)
     * Usado na criação de novos produtos
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            uuid: $data['uuid'] ?? null,
            name: $data['name'],
            slug: $data['slug'],
            price: $data['price'] instanceof MoneyVO ? $data['price'] : new MoneyVO($data['price']),
            categoryId: $data['category_id'],
            description: $data['description'] ?? null,
            imageUrl: $data['image_url'] ?? null,
            stock: $data['stock'] ?? 0,
            sku: $data['sku'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }

    /**
     * Verifica se o produto está disponível para venda
     * Produto disponível = ativo (is_active) E tem estoque (stock > 0)
     */
    public function isAvailable(): bool
    {
        return $this->isActive && $this->stock > 0;
    }

    /**
     * Decrementa o estoque
     */
    public function decrementStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('A quantidade deve ser maior que zero');
        }

        if ($this->stock < $quantity) {
            throw new \InvalidArgumentException('Estoque insuficiente');
        }

        $this->stock -= $quantity;
    }

    /**
     * Incrementa o estoque
     */
    public function incrementStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('A quantidade deve ser maior que zero');
        }

        $this->stock += $quantity;
    }

    /**
     * Ativa o produto
     */
    public function activate(): void
    {
        $this->isActive = true;
    }

    /**
     * Desativa o produto
     */
    public function deactivate(): void
    {
        $this->isActive = false;
    }

    /**
     * Converte para array para persistência
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => (int) ($this->price->toFloat() * 100), // Centavos
            'category_id' => $this->categoryId,
            'description' => $this->description,
            'image_url' => $this->imageUrl,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'is_active' => $this->isActive,
        ];
    }

    // ========================================
    // SETTERS (para UPDATE)
    // ========================================

    public function setName(string $name): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('O nome do produto é obrigatório');
        }

        if (strlen($name) > 200) {
            throw new \InvalidArgumentException('O nome do produto não pode ter mais de 200 caracteres');
        }

        $this->name = $name;
        $this->slug = \Illuminate\Support\Str::slug($name); // Atualiza slug automaticamente
    }

    public function setPrice(MoneyVO $price): void
    {
        if ($price->toFloat() < 0) {
            throw new \InvalidArgumentException('O preço não pode ser negativo');
        }

        $this->price = $price;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function setStock(int $stock): void
    {
        if ($stock < 0) {
            throw new \InvalidArgumentException('O estoque não pode ser negativo');
        }

        $this->stock = $stock;
    }

    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
