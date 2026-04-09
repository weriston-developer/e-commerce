<?php

namespace App\Application\DTOs\Inputs;

use App\Domain\Enums\UserRole;

/**
 * DTO para criação de usuário (Register)
 */
readonly class CreateUserInput
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password), // Hash da senha
            'role' => UserRole::CUSTOMER, // Role padrão
        ];
    }
}
