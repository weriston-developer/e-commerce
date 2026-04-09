<?php

namespace App\Infrastructure\Presentation\Controllers;

use App\Application\CQRS\Queries\Auth\GetAuthenticatedUserQuery;
use App\Application\CQRS\Queries\User\GetAllUsersQuery;
use App\Application\DTOs\Inputs\CreateUserInput;
use App\Application\DTOs\Inputs\LoginInput;
use App\Application\UseCases\Auth\LoginUserUseCase;
use App\Application\UseCases\Auth\LogoutUserUseCase;
use App\Application\UseCases\Auth\RefreshTokenUseCase;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Application\UseCases\User\DeleteUserUseCase;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\ListUsersRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller de Autenticação
 * 
 * Responsável por:
 * - Registro de usuários
 * - Login/Logout
 * - Refresh de token
 * - Dados do usuário autenticado
 */
class AuthController extends BaseController
{
    /**
     * Registrar novo usuário
     * 
     * POST /api/v1/auth/register
     */
    public function register(
        RegisterRequest $request,
        RegisterUserUseCase $registerUseCase
    ): JsonResponse {
        return $this->execute(function () use ($request, $registerUseCase) {
            // Validação já feita pelo FormRequest
            $input = CreateUserInput::fromArray($request->validated());

            // Executa UseCase
            $result = $registerUseCase->execute($input);

            return $this->successResponse(
                data: $result->toArray(),
                message: 'Usuário registrado com sucesso',
                code: 201
            );
        });
    }

    /**
     * Login de usuário
     * 
     * POST /api/v1/auth/login
     */
    public function login(
        LoginRequest $request,
        LoginUserUseCase $loginUseCase
    ): JsonResponse {
        return $this->execute(function () use ($request, $loginUseCase) {
            // Validação já feita pelo FormRequest
            $input = LoginInput::fromArray($request->validated());

            // Executa UseCase
            $result = $loginUseCase->execute($input);

            if (!$result) {
                return $this->errorResponse(
                    message: 'Credenciais inválidas',
                    code: 401
                );
            }

            return $this->successResponse(
                data: $result->toArray(),
                message: 'Login realizado com sucesso'
            );
        });
    }

    /**
     * Logout de usuário
     * 
     * POST /api/v1/auth/logout
     */
    public function logout(LogoutUserUseCase $logoutUseCase): JsonResponse
    {
        return $this->execute(function () use ($logoutUseCase) {
            $logoutUseCase->execute();

            return $this->successResponse(
                message: 'Logout realizado com sucesso'
            );
        });
    }

    /**
     * Refresh token JWT
     * 
     * POST /api/v1/auth/refresh
     */
    public function refresh(RefreshTokenUseCase $refreshUseCase): JsonResponse
    {
        return $this->execute(function () use ($refreshUseCase) {
            $token = $refreshUseCase->execute();

            if (!$token) {
                return $this->errorResponse(
                    message: 'Não foi possível renovar o token',
                    code: 401
                );
            }

            return $this->successResponse(
                data: [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('jwt.ttl') * 60,
                ],
                message: 'Token renovado com sucesso'
            );
        });
    }

    /**
     * Retorna dados do usuário autenticado
     * 
     * GET /api/v1/auth/me
     */
    public function me(
        Request $request,
        GetAuthenticatedUserQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($query) {
            /** @var \App\Infrastructure\Persistence\Models\User */
            $user = auth()->user();
            
            if (!$user) {
                return $this->errorResponse(
                    message: 'Usuário não autenticado',
                    code: 401
                );
            }

            $userOutput = $query->execute($user->id);

            return $this->successResponse(
                data: $userOutput?->toArray()
            );
        });
    }

    /**
     * Lista todos os usuários ativos (apenas admin)
     * 
     * GET /api/v1/users
     */
    public function listUsers(
        ListUsersRequest $request,
        GetAllUsersQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($request, $query) {
            $perPage = (int) $request->query('per_page', 15);
            $users = $query->execute($perPage);

            return $this->successResponse(
                data: $users
            );
        });
    }

    /**
     * Deletar usuário (apenas admin, não pode deletar admin nem a si mesmo)
     * 
     * DELETE /api/v1/users/{uuid}
     */
    public function deleteUser(
        string $uuid,
        DeleteUserRequest $request,
        DeleteUserUseCase $deleteUserUseCase
    ): JsonResponse {
        return $this->execute(function () use ($uuid, $deleteUserUseCase) {
            /** @var \App\Infrastructure\Persistence\Models\User */
            $authenticatedUser = auth()->user();
            
            $deleteUserUseCase->execute($uuid, $authenticatedUser->id);

            return $this->successResponse(
                message: 'Usuário deletado com sucesso',
                code: 200
            );
        });
    }
}
