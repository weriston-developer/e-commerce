<?php

declare(strict_types=1);

namespace App\Application\CQRS\Queries\User;

use App\Application\DTOs\Outputs\UserOutput;
use App\Domain\Interfaces\UserRepositoryInterface;

/**
 * Query para buscar todos os usuários ativos
 */
readonly class GetAllUsersQuery
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(int $perPage = 15): array
    {
        $users = $this->userRepository->getAll($perPage);

        return [
            'data' => $users->map(fn($user) => UserOutput::fromModel($user)->toArray()),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ];
    }
}
