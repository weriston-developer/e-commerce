<?php

namespace App\Application\DTOs\Inputs;

/**
 * DTO para login
 */
readonly class LoginInput
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
