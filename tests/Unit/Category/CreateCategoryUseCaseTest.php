<?php

use App\Application\DTOs\Inputs\CreateCategoryInput;
use App\Application\Errors\CategorySlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Application\UseCases\Category\CreateCategoryUseCase;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category;
use Mockery;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
    $this->useCase = new CreateCategoryUseCase($this->categoryRepository);
});

afterEach(function () {
    Mockery::close();
});

test('deve criar categoria com sucesso', function () {
    $input = new CreateCategoryInput(
        name: 'Eletrônicos',
        description: 'Produtos eletrônicos',
        isActive: true
    );

    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'category-uuid';
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('slugExists')
        ->once()
        ->with('eletronicos')
        ->andReturn(false);

    $this->categoryRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['name'] === 'Eletrônicos'
                && $data['slug'] === 'eletronicos'
                && $data['description'] === 'Produtos eletrônicos'
                && $data['is_active'] === true;
        }))
        ->andReturn($category);

    $result = $this->useCase->execute($input);

    expect($result)->toBeInstanceOf(\App\Application\DTOs\Outputs\CategoryOutput::class)
        ->and($result->uuid)->toBe('category-uuid')
        ->and($result->name)->toBe('Eletrônicos')
        ->and($result->slug)->toBe('eletronicos')
        ->and($result->description)->toBe('Produtos eletrônicos')
        ->and($result->isActive)->toBe(true);
});

test('deve lançar exceção quando slug já existe', function () {
    $input = new CreateCategoryInput(
        name: 'Eletrônicos',
        description: 'Produtos eletrônicos',
        isActive: true
    );

    $this->categoryRepository
        ->shouldReceive('slugExists')
        ->once()
        ->with('eletronicos')
        ->andReturn(true);

    $this->categoryRepository
        ->shouldNotReceive('create');

    expect(fn() => $this->useCase->execute($input))
        ->toThrow(EcommerceException::class);
});

test('deve gerar slug automaticamente a partir do nome', function () {
    $input = new CreateCategoryInput(
        name: 'Roupas & Acessórios',
        description: 'Moda em geral',
        isActive: true
    );

    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'category-uuid';
    $category->name = 'Roupas & Acessórios';
    $category->slug = 'roupas-acessorios';
    $category->description = 'Moda em geral';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('slugExists')
        ->once()
        ->with('roupas-acessorios')
        ->andReturn(false);

    $this->categoryRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['slug'] === 'roupas-acessorios';
        }))
        ->andReturn($category);

    $result = $this->useCase->execute($input);

    expect($result->slug)->toBe('roupas-acessorios');
});

test('deve usar isActive padrão como true quando não fornecido', function () {
    $input = new CreateCategoryInput(
        name: 'Livros',
        description: 'Livros diversos'
    );

    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'category-uuid';
    $category->name = 'Livros';
    $category->slug = 'livros';
    $category->description = 'Livros diversos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('slugExists')
        ->once()
        ->with('livros')
        ->andReturn(false);

    $this->categoryRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['is_active'] === true;
        }))
        ->andReturn($category);

    $result = $this->useCase->execute($input);

    expect($result->isActive)->toBe(true);
});

test('deve aceitar description como null', function () {
    $input = new CreateCategoryInput(
        name: 'Diversos',
        description: null,
        isActive: true
    );

    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'category-uuid';
    $category->name = 'Diversos';
    $category->slug = 'diversos';
    $category->description = null;
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('slugExists')
        ->once()
        ->with('diversos')
        ->andReturn(false);

    $this->categoryRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['description'] === null;
        }))
        ->andReturn($category);

    $result = $this->useCase->execute($input);

    expect($result->description)->toBeNull();
});
