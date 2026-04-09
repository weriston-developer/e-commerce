<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Infrastructure\Persistence\Models\Category;

/**
 * Entidade de Categoria
 * Contém as regras de negócio do domínio
 */
class CategoryEntity
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public string $name,
        public string $slug,
        public ?string $description = null,
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
            throw new \InvalidArgumentException('O nome da categoria é obrigatório');
        }

        if (strlen($this->name) > 100) {
            throw new \InvalidArgumentException('O nome da categoria não pode ter mais de 100 caracteres');
        }

        if (empty($this->slug)) {
            throw new \InvalidArgumentException('O slug da categoria é obrigatório');
        }

        if (strlen($this->slug) > 100) {
            throw new \InvalidArgumentException('O slug da categoria não pode ter mais de 100 caracteres');
        }
    }

    /**
     * Cria entidade a partir do Model (vindo do banco)
     */
    public static function fromModel(Category $model): self
    {
        return new self(
            id: $model->id,
            uuid: $model->uuid,
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            isActive: $model->is_active,
        );
    }

    /**
     * Cria entidade a partir de array (dados estruturados do sistema)
     * Usado na criação de novas categorias
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            uuid: $data['uuid'] ?? null,
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }

    /**
     * Ativa a categoria
     */
    public function activate(): void
    {
        $this->isActive = true;
    }

    /**
     * Desativa a categoria
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
            'description' => $this->description,
            'is_active' => $this->isActive,
        ];
    }

    // ========================================
    // SETTERS (para UPDATE)
    // ========================================

    public function setName(string $name): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('O nome da categoria é obrigatório');
        }

        if (strlen($name) > 100) {
            throw new \InvalidArgumentException('O nome da categoria não pode ter mais de 100 caracteres');
        }

        $this->name = $name;
        $this->slug = \Illuminate\Support\Str::slug($name); // Atualiza slug automaticamente
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
