<?php

declare(strict_types=1);

namespace App\Domain\Entities;

use App\Domain\Enums\UserRole;
use App\Infrastructure\Persistence\Models\User;

/**
 * Entidade de Usuário
 * Contém as regras de negócio do domínio
 */
class UserEntity
{
    public function __construct(
        public ?int $id,
        public ?string $uuid,
        public string $name,
        public string $email,
        public string $password,
        public UserRole $role,
    ) {
        $this->validate();
    }

    /**
     * Validações de regras de negócio
     */
    private function validate(): void
    {
        if (empty($this->name)) {
            throw new \InvalidArgumentException('O nome é obrigatório');
        }

        if (strlen($this->name) > 255) {
            throw new \InvalidArgumentException('O nome não pode ter mais de 255 caracteres');
        }

        if (empty($this->email)) {
            throw new \InvalidArgumentException('O email é obrigatório');
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }

        if (strlen($this->email) > 255) {
            throw new \InvalidArgumentException('O email não pode ter mais de 255 caracteres');
        }
    }

    /**
     * Cria entidade a partir do Model (vindo do banco)
     */
    public static function fromModel(User $model): self
    {
        return new self(
            id: $model->id,
            uuid: $model->uuid,
            name: $model->name,
            email: $model->email,
            password: $model->password,
            role: $model->role,
        );
    }

    /**
     * Cria entidade a partir de array (dados estruturados do sistema)
     * Usado na criação de novos usuários
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            uuid: $data['uuid'] ?? null,
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: $data['role'] ?? UserRole::USER,
        );
    }

    /**
     * Verifica se o usuário é admin
     */
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    /**
     * Verifica se o usuário é comum
     */
    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    /**
     * Verifica se pode ser deletado
     * Regra: Não pode deletar usuários admin
     */
    public function canBeDeleted(): bool
    {
        return !$this->isAdmin();
    }

    /**
     * Verifica se é o mesmo usuário (por ID)
     */
    public function isSameUser(int $userId): bool
    {
        return $this->id === $userId;
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
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
        ];
    }

    // ========================================
    // SETTERS (para UPDATE)
    // ========================================

    public function setName(string $name): void
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('O nome é obrigatório');
        }

        if (strlen($name) > 255) {
            throw new \InvalidArgumentException('O nome não pode ter mais de 255 caracteres');
        }

        $this->name = $name;
    }

    public function setEmail(string $email): void
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('O email é obrigatório');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email inválido');
        }

        if (strlen($email) > 255) {
            throw new \InvalidArgumentException('O email não pode ter mais de 255 caracteres');
        }

        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRole(UserRole $role): void
    {
        $this->role = $role;
    }
}
