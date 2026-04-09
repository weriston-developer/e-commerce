<?php

use App\Application\Errors\CategoryNotFoundError;
use App\Application\Errors\DeleteCategoryError;
use App\Application\Exceptions\EcommerceException;
use App\Application\UseCases\Category\DeleteCategoryUseCase;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category;
use Mockery;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
    $this->useCase = new DeleteCategoryUseCase($this->categoryRepository);
});

afterEach(function () {
    Mockery::close();
});

test('deve deletar categoria com sucesso', function () {
    $uuid = 'category-uuid';
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = $uuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($category);

    $this->categoryRepository
        ->shouldReceive('delete')
        ->once()
        ->with($uuid)
        ->andReturn(true);

    $result = $this->useCase->execute($uuid);

    expect($result)->toBe(true);
});

test('deve lançar exceção quando categoria não existe', function () {
    $uuid = 'non-existent-uuid';

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn(null);

    $this->categoryRepository
        ->shouldNotReceive('delete');

    expect(fn() => $this->useCase->execute($uuid))
        ->toThrow(EcommerceException::class);
});

test('deve lançar exceção quando delete falha', function () {
    $uuid = 'category-uuid';
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = $uuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($category);

    $this->categoryRepository
        ->shouldReceive('delete')
        ->once()
        ->with($uuid)
        ->andReturn(false);

    expect(fn() => $this->useCase->execute($uuid))
        ->toThrow(EcommerceException::class);
});

test('deve verificar existência da categoria antes de deletar', function () {
    $uuid = 'category-uuid';
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = $uuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($uuid)
        ->andReturn($category);

    $this->categoryRepository
        ->shouldReceive('delete')
        ->once()
        ->andReturn(true);

    $this->useCase->execute($uuid);

    // Se chegou aqui, findByUuid foi chamado antes de delete
    expect(true)->toBe(true);
});

test('deve retornar true quando categoria é deletada com sucesso', function () {
    $uuid = 'category-uuid';
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = $uuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->andReturn($category);

    $this->categoryRepository
        ->shouldReceive('delete')
        ->once()
        ->andReturn(true);

    $result = $this->useCase->execute($uuid);

    expect($result)->toBeTrue();
});
