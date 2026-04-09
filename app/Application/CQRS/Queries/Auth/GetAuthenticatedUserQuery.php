<?php

namespace App\Application\CQRS\Queries\Auth;

use App\Application\DTOs\Outputs\UserOutput;
use App\Domain\Interfaces\UserRepositoryInterface;

/**
 * Query para buscar usuário autenticado por UUID
 */
readonly class GetAuthenticatedUserQuery
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $uuid): ?UserOutput
    {
        $user = $this->userRepository->findById($uuid);

        if (!$user) {
            return null;
        }

        return UserOutput::fromModel($user);
    }
}
