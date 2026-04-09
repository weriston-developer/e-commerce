<?php

declare(strict_types=1);

namespace App\Application\UseCases\User;

use App\Application\Errors\CannotDeleteAdminUserError;
use App\Application\Errors\CannotDeleteSelfError;
use App\Application\Errors\DeleteUserError;
use App\Application\Errors\UserNotFoundError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Entities\UserEntity;
use App\Domain\Interfaces\UserRepositoryInterface;


readonly class DeleteUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $uuid, int $authenticatedUserId): bool
    {
        $userModel = $this->userRepository->findByUuid($uuid);
        
        if (!$userModel) {
            throw new EcommerceException(UserNotFoundError::class);
        }

        $userEntity = UserEntity::fromModel($userModel);

        if (!$userEntity->canBeDeleted()) {
            throw new EcommerceException(CannotDeleteAdminUserError::class);
        }

        if ($userEntity->isSameUser($authenticatedUserId)) {
            throw new EcommerceException(CannotDeleteSelfError::class);
        }

        $deleted = $this->userRepository->delete($uuid);

        if(!$deleted) {
            throw new EcommerceException(DeleteUserError::class);
        }
        
        return $deleted;
    }
}
