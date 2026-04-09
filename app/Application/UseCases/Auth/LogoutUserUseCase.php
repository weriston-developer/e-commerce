<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Services\JwtTokenService;

/**
 * UseCase para logout de usuário
 */
readonly class LogoutUserUseCase
{
    public function __construct(
        private JwtTokenService $jwtTokenService,
    ) {}

    public function execute(): bool
    {
        return $this->jwtTokenService->invalidateToken();
    }
}
