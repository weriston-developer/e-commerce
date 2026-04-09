<?php

namespace App\Application\UseCases\Auth;

use App\Domain\Services\JwtTokenService;

/**
 * UseCase para refresh de token JWT
 */
readonly class RefreshTokenUseCase
{
    public function __construct(
        private JwtTokenService $jwtTokenService,
    ) {}

    public function execute(): ?string
    {
        return $this->jwtTokenService->refreshToken();
    }
}
