<?php

use App\Application\DTOs\Inputs\UpdateCategoryInput;
use App\Application\Errors\CategoryNotFoundError;
use App\Application\Errors\CategorySlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Application\UseCases\Category\UpdateCategoryUseCase;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category;
use Mockery;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
    $this->useCase = new UpdateCategoryUseCase($this->categoryRepository);
});

afterEach(function () {
    Mockery::close();
});

test('deve atualizar categoria com sucesso', function () {
    $uuid = 'category-uuid';
    
    $existingCategory = Mockery::mock(Category::class)->makePartial();
    $existingCategory->id = 1;
    $existingCategory->uuid = $uuid;
    $existingCategory->name = 'Eletrônicos';
    $existingCategory->slug = 'eletronicos';
    $existingCategory->description = 'Produtos eletrônicos';
    $existingCategory->is_active = true;
    $existingCategory->created_at = now();
    $existingCategory->updated_at = now();
    $existingCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $updatedCategory = Mockery::mock(Category::class)->makePartial();
    $updatedCategory->id = 1;
    $updatedCategory->uuid = $uuid;
    $updatedCategory->name = 'Eletrônicos e Informática';
    $updatedCategory->slug = 'eletronicos-e-informatica';
    $updatedCategory->description = 'Produtos eletrônicos e informática';
    $updatedCategory->is_active = false;
    $updatedCategory->created_at = now();
    $updatedCategory->updated_at = now();
    $updatedCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $input = new UpdateCategoryInput(
        name: 'Eletrônicos e Informática',
        description: 'Produtos eletrônicos e informática',
        isActive: false
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($existingCategory);

    $this->categoryRepository
        ->shouldReceive('slugExists')
        ->once()
        ->with('eletronicos-e-informatica', 1)
        ->andReturn(false);

    $this->categoryRepository
        ->shouldReceive('update')
        ->once()
        ->with($uuid, Mockery::on(function ($data) {
            return $data['name'] === 'Eletrônicos e Informática'
                && $data['slug'] === 'eletronicos-e-informatica'
                && $data['description'] === 'Produtos eletrônicos e informática'
                && $data['is_active'] === false;
        }))
        ->andReturn($updatedCategory);

    $result = $this->useCase->execute($uuid, $input);

    expect($result)->toBeInstanceOf(\App\Application\DTOs\Outputs\CategoryOutput::class)
        ->and($result->name)->toBe('Eletrônicos e Informática')
        ->and($result->slug)->toBe('eletronicos-e-informatica')
        ->and($result->isActive)->toBe(false);
});

test('deve lançar exceção quando categoria não existe', function () {
    $uuid = 'non-existent-uuid';

    $input = new UpdateCategoryInput(
        name: 'Nova Categoria'
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn(null);

    $this->categoryRepository
        ->shouldNotReceive('slugExists');

    $this->categoryRepository
        ->shouldNotReceive('update');

    expect(fn() => $this->useCase->execute($uuid, $input))
        ->toThrow(EcommerceException::class);
});

test('deve atualizar apenas campos fornecidos', function () {
    $uuid = 'category-uuid';
    
    $existingCategory = Mockery::mock(Category::class)->makePartial();
    $existingCategory->id = 1;
    $existingCategory->uuid = $uuid;
    $existingCategory->name = 'Eletrônicos';
    $existingCategory->slug = 'eletronicos';
    $existingCategory->description = 'Produtos eletrônicos';
    $existingCategory->is_active = true;
    $existingCategory->created_at = now();
    $existingCategory->updated_at = now();
    $existingCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $updatedCategory = Mockery::mock(Category::class)->makePartial();
    $updatedCategory->id = 1;
    $updatedCategory->uuid = $uuid;
    $updatedCategory->name = 'Eletrônicos';
    $updatedCategory->slug = 'eletronicos';
    $updatedCategory->description = 'Nova descrição';
    $updatedCategory->is_active = true;
    $updatedCategory->created_at = now();
    $updatedCategory->updated_at = now();
    $updatedCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $input = new UpdateCategoryInput(
        description: 'Nova descrição'
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($existingCategory);

    $this->categoryRepository
        ->shouldNotReceive('slugExists');

    $this->categoryRepository
        ->shouldReceive('update')
        ->once()
        ->with($uuid, Mockery::on(function ($data) {
            return $data['description'] === 'Nova descrição';
        }))
        ->andReturn($updatedCategory);

    $result = $this->useCase->execute($uuid, $input);

    expect($result->description)->toBe('Nova descrição');
});

test('deve lançar exceção quando novo slug já existe', function () {
    $uuid = 'category-uuid';
    
    $existingCategory = Mockery::mock(Category::class)->makePartial();
    $existingCategory->id = 1;
    $existingCategory->uuid = $uuid;
    $existingCategory->name = 'Eletrônicos';
    $existingCategory->slug = 'eletronicos';
    $existingCategory->description = 'Produtos eletrônicos';
    $existingCategory->is_active = true;
    $existingCategory->created_at = now();
    $existingCategory->updated_at = now();
    $existingCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $input = new UpdateCategoryInput(
        name: 'Livros'
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($existingCategory);

    $this->categoryRepository
        ->shouldReceive('slugExists')
        ->once()
        ->with('livros', 1)
        ->andReturn(true);

    $this->categoryRepository
        ->shouldNotReceive('update');

    expect(fn() => $this->useCase->execute($uuid, $input))
        ->toThrow(EcommerceException::class);
});

test('não deve verificar slug quando nome não foi alterado', function () {
    $uuid = 'category-uuid';
    
    $existingCategory = Mockery::mock(Category::class)->makePartial();
    $existingCategory->id = 1;
    $existingCategory->uuid = $uuid;
    $existingCategory->name = 'Eletrônicos';
    $existingCategory->slug = 'eletronicos';
    $existingCategory->description = 'Produtos eletrônicos';
    $existingCategory->is_active = true;
    $existingCategory->created_at = now();
    $existingCategory->updated_at = now();
    $existingCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $updatedCategory = Mockery::mock(Category::class)->makePartial();
    $updatedCategory->id = 1;
    $updatedCategory->uuid = $uuid;
    $updatedCategory->name = 'Eletrônicos';
    $updatedCategory->slug = 'eletronicos';
    $updatedCategory->description = 'Produtos eletrônicos';
    $updatedCategory->is_active = false;
    $updatedCategory->created_at = now();
    $updatedCategory->updated_at = now();
    $updatedCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $input = new UpdateCategoryInput(
        isActive: false
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($existingCategory);

    $this->categoryRepository
        ->shouldNotReceive('slugExists');

    $this->categoryRepository
        ->shouldReceive('update')
        ->once()
        ->with($uuid, Mockery::on(function ($data) {
            return isset($data['is_active']) && $data['is_active'] === false;
        }))
        ->andReturn($updatedCategory);

    $result = $this->useCase->execute($uuid, $input);

    expect($result->isActive)->toBe(false);
});

test('deve permitir desativar categoria', function () {
    $uuid = 'category-uuid';
    
    $existingCategory = Mockery::mock(Category::class)->makePartial();
    $existingCategory->id = 1;
    $existingCategory->uuid = $uuid;
    $existingCategory->name = 'Eletrônicos';
    $existingCategory->slug = 'eletronicos';
    $existingCategory->description = 'Produtos eletrônicos';
    $existingCategory->is_active = true;
    $existingCategory->created_at = now();
    $existingCategory->updated_at = now();
    $existingCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $updatedCategory = Mockery::mock(Category::class)->makePartial();
    $updatedCategory->id = 1;
    $updatedCategory->uuid = $uuid;
    $updatedCategory->name = 'Eletrônicos';
    $updatedCategory->slug = 'eletronicos';
    $updatedCategory->description = 'Produtos eletrônicos';
    $updatedCategory->is_active = false;
    $updatedCategory->created_at = now();
    $updatedCategory->updated_at = now();
    $updatedCategory->allows()->setAttribute(Mockery::any(), Mockery::any());

    $input = new UpdateCategoryInput(
        isActive: false
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($existingCategory);

    $this->categoryRepository
        ->shouldReceive('update')
        ->once()
        ->andReturn($updatedCategory);

    $result = $this->useCase->execute($uuid, $input);

    expect($result->isActive)->toBe(false);
});
