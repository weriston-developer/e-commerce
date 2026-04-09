<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Errors\UserNotFoundError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Infrastructure\Persistence\Models\User;

/**
 * Implementação do Repository de Usuários usando Eloquent
 */
class UserRepository implements UserRepositoryInterface
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(string $uuid, array $data): User
    {
        $user = $this->findByUuid($uuid);
        
        if (!$user) {
            throw new EcommerceException(UserNotFoundError::class);
        }

        $user->update($data);
        return $user->fresh();
    }

    public function delete(string $uuid): bool
    {
        $user = $this->findByUuid($uuid);
        
        if (!$user) {
            return false;
        }

        return $user->delete();
    }

    public function emailExists(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function findByUuid(string $uuid): ?User
    {
        return User::where('uuid', $uuid)->first();
    }

    public function getAll(int $perPage = 15)
    {
        return User::orderBy('created_at', 'desc')->paginate($perPage);
    }
}
