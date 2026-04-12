<?php

use App\Application\DTOs\Inputs\LoginInput;
use App\Application\UseCases\Auth\LoginUserUseCase;
use App\Domain\Enums\UserRole;
use App\Domain\Services\JwtTokenService;
use App\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->jwtTokenService = Mockery::mock(JwtTokenService::class);
    $this->app->instance(JwtTokenService::class, $this->jwtTokenService);
    
    $this->useCase = app(LoginUserUseCase::class);
});

afterEach(function () {
    Mockery::close();
});

test('deve fazer login com credenciais válidas', function () {
    // Arrange
    $email = 'usuario@example.com';
    $password = 'password123';
    $token = 'jwt-token-mock';
    $ttl = 3600;
    
    // Mocka o User com Mockery permitindo atribuições
    $user = Mockery::mock(User::class)->makePartial();
    $user->uuid = 'uuid-123';
    $user->name = 'Usuario Teste';
    $user->email = $email;
    $user->role = UserRole::USER;
    $user->email_verified_at = null;
    $user->created_at = now();
    
    // Permite que setAttribute() funcione normalmente
    $user->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $input = new LoginInput($email, $password);
    
    // Mock do JWTAuth::attempt() retornando token
    JWTAuth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password])
        ->andReturn($token);
    
    // Mock do Auth::user() retornando usuário
    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);
    
    // Mock do JwtTokenService
    $this->jwtTokenService
        ->shouldReceive('getTokenTTL')
        ->once()
        ->andReturn($ttl);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result)->not->toBeNull()
        ->and($result->user->uuid)->toBe('uuid-123')
        ->and($result->user->email)->toBe($email)
        ->and($result->accessToken)->toBe($token)
        ->and($result->expiresIn)->toBe($ttl);
});

test('deve retornar null quando credenciais são inválidas', function () {
    // Arrange
    $email = 'usuario@example.com';
    $password = 'senhaerrada';
    
    $input = new LoginInput($email, $password);
    
    // Mock do JWTAuth::attempt() retornando false (credenciais inválidas)
    JWTAuth::shouldReceive('attempt')
        ->once()
        ->with(['email' => $email, 'password' => $password])
        ->andReturn(false);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result)->toBeNull();
});

test('deve retornar AuthOutput com dados corretos', function () {
    // Arrange
    $email = 'admin@example.com';
    $password = 'admin123';
    $token = 'admin-jwt-token';
    $ttl = 7200;
    
    $user = Mockery::mock(User::class)->makePartial();
    $user->uuid = 'admin-uuid';
    $user->name = 'Admin User';
    $user->email = $email;
    $user->role = UserRole::ADMIN;
    $user->email_verified_at = now();
    $user->created_at = now();
    $user->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $input = new LoginInput($email, $password);
    
    JWTAuth::shouldReceive('attempt')
        ->once()
        ->andReturn($token);
    
    Auth::shouldReceive('user')
        ->once()
        ->andReturn($user);
    
    $this->jwtTokenService
        ->shouldReceive('getTokenTTL')
        ->once()
        ->andReturn($ttl);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result->accessToken)->toBe($token)
        ->and($result->expiresIn)->toBe($ttl)
        ->and($result->tokenType)->toBe('Bearer')
        ->and($result->user)->not->toBeNull()
        ->and($result->user->email)->toBe($email)
        ->and($result->user->name)->toBe('Admin User');
});

