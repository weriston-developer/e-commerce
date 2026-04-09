<?php

declare(strict_types=1);

namespace App\Domain\Enums;

/**
 * Enum de Roles (Perfis) de Usuários
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';

    /**
     * Retorna o label legível
     */
    public function label(): string
    {
        return match($this) {
            self::ADMIN => 'Administrador',
            self::CUSTOMER => 'Cliente',
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
     * Verifica se é customer
     */
    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    /**
     * Retorna todos os valores possíveis
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
