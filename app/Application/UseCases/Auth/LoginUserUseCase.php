<?php

namespace App\Application\UseCases\Auth;

use App\Application\DTOs\Inputs\LoginInput;
use App\Application\DTOs\Outputs\AuthOutput;
use App\Application\DTOs\Outputs\UserOutput;
use App\Domain\Services\JwtTokenService;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * UseCase para login de usuário
 */
readonly class LoginUserUseCase
{
    public function __construct(
        private JwtTokenService $jwtTokenService,
    ) {}

    public function execute(LoginInput $input): ?AuthOutput
    {
        // Tenta autenticar com JWT
        $credentials = $input->toArray();

        if (!$token = JWTAuth::attempt($credentials)) {
            return null; // Credenciais inválidas
        }

        // Pega usuário autenticado
        $user = auth()->user();

        return new AuthOutput(
            user: UserOutput::fromModel($user),
            accessToken: $token,
            tokenType: 'Bearer',
            expiresIn: $this->jwtTokenService->getTokenTTL(),
        );
    }
}
