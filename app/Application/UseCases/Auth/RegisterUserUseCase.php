<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Inputs\CreateUserInput;
use App\Application\DTOs\Outputs\AuthOutput;
use App\Application\DTOs\Outputs\UserOutput;
use App\Application\Errors\EmailAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\Services\JwtTokenService;

/**
 * UseCase para registrar novo usuário
 */
readonly class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtTokenService $jwtTokenService,
    ) {}

    public function execute(CreateUserInput $input): AuthOutput
    {
        // Verifica se email já existe
        if ($this->userRepository->emailExists($input->email)) {
            throw new EcommerceException(EmailAlreadyExistsError::class);
        }

        // Cria usuário
        $user = $this->userRepository->create($input->toArray());

        // Gera token JWT
        $token = $this->jwtTokenService->generateToken($user);

        return new AuthOutput(
            user: UserOutput::fromModel($user),
            accessToken: $token,
            tokenType: 'Bearer',
            expiresIn: $this->jwtTokenService->getTokenTTL(),
        );
    }
}
