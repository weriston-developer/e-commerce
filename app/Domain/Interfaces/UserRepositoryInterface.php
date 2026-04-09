<?php

namespace App\Domain\Interfaces;

use App\Infrastructure\Persistence\Models\User;

/**
 * Interface para Repository de Usuários
 * 
 * Define o contrato que a implementação deve seguir (Inversão de Dependência)
 */
interface UserRepositoryInterface
{
    /**
     * Busca usuário por ID
     */
    public function findById(string $id): ?User;

    /**
     * Busca usuário por email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Cria novo usuário
     */
    public function create(array $data): User;

    /**
     * Atualiza usuário
     */
    public function update(string $id, array $data): User;

    /**
     * Deleta usuário (soft delete)
     */
    public function delete(string $uuid): bool;

    /**
     * Verifica se email já existe
     */
    public function emailExists(string $email): bool;

    /**
     * Busca usuário por UUID
     */
    public function findByUuid(string $uuid): ?User;

    /**
     * Busca todos os usuários ativos com paginação
     */
    public function getAll(int $perPage = 15);
}
