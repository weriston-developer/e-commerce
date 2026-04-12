<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * Enum de Roles (Perfis) de Usuários
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case USER = 'user';

    /**
     * Retorna o label legível
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::USER => 'Usuário',
        };
    }

    /**
     * Verifica se é admin
     */
    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    /**
     * Verifica se é usuário comum
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }

    /**
     * Retorna todos os valores possíveis
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
