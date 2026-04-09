<?php

namespace App\Application\DTOs\Outputs;

/**
 * DTO para retornar dados de autenticação (token JWT)
 */
readonly class AuthOutput
{
    public function __construct(
        public UserOutput $user,
        public string $accessToken,
        public string $tokenType = 'Bearer',
        public int $expiresIn = 3600,
    ) {}

    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'access_token' => $this->accessToken,
            'token_type' => $this->tokenType,
            'expires_in' => $this->expiresIn,
        ];
    }
}
