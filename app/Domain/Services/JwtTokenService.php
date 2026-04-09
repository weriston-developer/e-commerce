<?php

declare(strict_types=1);

namespace App\Domain\Services;

use App\Infrastructure\Persistence\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Serviço de domínio responsável pela geração de tokens JWT
 */
class JwtTokenService
{
    /**
     * Gera token JWT a partir de um usuário
     */
    public function generateToken(User $user): string
    {
        return JWTAuth::fromUser($user);
    }

    /**
     * Valida e retorna o usuário a partir do token
     */
    public function getUserFromToken(): ?User
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Invalida o token atual (logout)
     */
    public function invalidateToken(): bool
    {
        try {
            JWTAuth::parseToken()->invalidate();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Atualiza o token (refresh)
     */
    public function refreshToken(): ?string
    {
        try {
            return JWTAuth::parseToken()->refresh();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Obtém o tempo de expiração do token em segundos
     */
    public function getTokenTTL(): int
    {
        return config('jwt.ttl') * 60; // Converte minutos para segundos
    }
}
