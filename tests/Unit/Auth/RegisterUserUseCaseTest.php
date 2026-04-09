<?php

use App\Application\DTOs\Inputs\CreateUserInput;
use App\Application\Errors\EmailAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Domain\Enums\UserRole;
use App\Domain\Interfaces\UserRepositoryInterface;
use App\Domain\Services\JwtTokenService;
use App\Infrastructure\Persistence\Models\User;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
    $this->jwtTokenService = Mockery::mock(JwtTokenService::class);
    
    $this->app->instance(UserRepositoryInterface::class, $this->userRepository);
    $this->app->instance(JwtTokenService::class, $this->jwtTokenService);
    
    $this->useCase = app(RegisterUserUseCase::class);
});

afterEach(function () {
    Mockery::close();
});

test('deve registrar novo usuário com sucesso', function () {
    // Arrange
    $name = 'Novo Usuario';
    $email = 'novo@example.com';
    $password = 'password123';
    $token = 'jwt-token-mock';
    $ttl = 3600;
    
    $input = new CreateUserInput($name, $email, $password);
    
    // Mock do User criado
    $user = Mockery::mock(User::class)->makePartial();
    $user->uuid = 'new-uuid-123';
    $user->name = $name;
    $user->email = $email;
    $user->role = UserRole::CUSTOMER;
    $user->email_verified_at = null;
    $user->created_at = now();
    $user->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Expectativas
    $this->userRepository
        ->shouldReceive('emailExists')
        ->once()
        ->with($email)
        ->andReturn(false);
    
    $this->userRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) use ($name, $email) {
            return $data['name'] === $name
                && $data['email'] === $email
                && isset($data['password']) // password foi hasheado
                && $data['role'] === UserRole::CUSTOMER;
        }))
        ->andReturn($user);
    
    $this->jwtTokenService
        ->shouldReceive('generateToken')
        ->once()
        ->with($user)
        ->andReturn($token);
    
    $this->jwtTokenService
        ->shouldReceive('getTokenTTL')
        ->once()
        ->andReturn($ttl);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result)->not->toBeNull()
        ->and($result->user->uuid)->toBe('new-uuid-123')
        ->and($result->user->name)->toBe($name)
        ->and($result->user->email)->toBe($email)
        ->and($result->accessToken)->toBe($token)
        ->and($result->tokenType)->toBe('Bearer')
        ->and($result->expiresIn)->toBe($ttl);
});

test('deve lançar exceção quando email já existe', function () {
    // Arrange
    $name = 'Usuario Existente';
    $email = 'existente@example.com';
    $password = 'password123';
    
    $input = new CreateUserInput($name, $email, $password);
    
    // Expectativas
    $this->userRepository
        ->shouldReceive('emailExists')
        ->once()
        ->with($email)
        ->andReturn(true);
    
    // Não deve chamar create se email já existe
    $this->userRepository
        ->shouldNotReceive('create');
    
    $this->jwtTokenService
        ->shouldNotReceive('generateToken');
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($input))
        ->toThrow(EcommerceException::class);
});

test('deve criar usuário com role CUSTOMER por padrão', function () {
    // Arrange
    $name = 'Cliente Teste';
    $email = 'cliente@example.com';
    $password = 'password123';
    $token = 'customer-token';
    $ttl = 3600;
    
    $input = new CreateUserInput($name, $email, $password);
    
    $user = Mockery::mock(User::class)->makePartial();
    $user->uuid = 'customer-uuid';
    $user->name = $name;
    $user->email = $email;
    $user->role = UserRole::CUSTOMER;
    $user->email_verified_at = null;
    $user->created_at = now();
    $user->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $this->userRepository
        ->shouldReceive('emailExists')
        ->once()
        ->andReturn(false);
    
    $this->userRepository
        ->shouldReceive('create')
        ->once()
        ->andReturn($user);
    
    $this->jwtTokenService
        ->shouldReceive('generateToken')
        ->once()
        ->andReturn($token);
    
    $this->jwtTokenService
        ->shouldReceive('getTokenTTL')
        ->once()
        ->andReturn($ttl);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result->user)->not->toBeNull()
        ->and($result->user->email)->toBe($email)
        ->and($result->accessToken)->toBe($token);
});

test('deve retornar AuthOutput com todos os campos obrigatórios', function () {
    // Arrange
    $input = new CreateUserInput('Test User', 'test@example.com', 'pass123');
    
    $user = Mockery::mock(User::class)->makePartial();
    $user->uuid = 'test-uuid';
    $user->name = 'Test User';
    $user->email = 'test@example.com';
    $user->role = UserRole::CUSTOMER;
    $user->email_verified_at = null;
    $user->created_at = now();
    $user->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $this->userRepository->shouldReceive('emailExists')->andReturn(false);
    $this->userRepository->shouldReceive('create')->andReturn($user);
    $this->jwtTokenService->shouldReceive('generateToken')->andReturn('token-123');
    $this->jwtTokenService->shouldReceive('getTokenTTL')->andReturn(7200);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result)->toHaveProperty('user')
        ->and($result)->toHaveProperty('accessToken')
        ->and($result)->toHaveProperty('tokenType')
        ->and($result)->toHaveProperty('expiresIn')
        ->and($result->tokenType)->toBe('Bearer')
        ->and($result->expiresIn)->toBeInt()
        ->and($result->expiresIn)->toBeGreaterThan(0);
});
