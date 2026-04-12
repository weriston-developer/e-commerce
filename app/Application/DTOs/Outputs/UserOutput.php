<?php

namespace App\Application\DTOs\Outputs;

use App\Infrastructure\Persistence\Models\User;

/**
 * DTO para retornar dados do usuário
 */
readonly class UserOutput
{
    public function __construct(
        public string $uuid,
        public string $name,
        public string $email,
        public string $role,
        public ?string $emailVerifiedAt,
        public string $createdAt,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            uuid: $user->uuid,
            name: $user->name,
            email: $user->email,
            role: $user->role->value,
            emailVerifiedAt: $user->email_verified_at?->toIso8601String(),
            createdAt: $user->created_at->toIso8601String(),
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at' => $this->emailVerifiedAt,
            'created_at' => $this->createdAt,
        ];
    }
}
