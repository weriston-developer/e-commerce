<?php

use App\Application\Errors\DeleteProductError;
use App\Application\Errors\ProductNotFoundError;
use App\Application\Exceptions\EcommerceException;
use App\Application\UseCases\Product\DeleteProductUseCase;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Models\Product;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
    
    $this->app->instance(ProductRepositoryInterface::class, $this->productRepository);
    
    $this->useCase = app(DeleteProductUseCase::class);
});

afterEach(function () {
    Mockery::close();
});

test('deve deletar produto com sucesso', function () {
    // Arrange
    $productUuid = 'product-uuid-123';
    
    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = $productUuid;
    $product->name = 'Produto a ser deletado';
    $product->slug = 'produto-a-ser-deletado';
    $product->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($productUuid)
        ->andReturn($product);
    
    $this->productRepository
        ->shouldReceive('delete')
        ->once()
        ->with($productUuid)
        ->andReturn(true);
    
    // Act
    $result = $this->useCase->execute($productUuid);
    
    // Assert
    expect($result)->toBeTrue();
});

test('deve lançar exceção quando produto não existe', function () {
    // Arrange
    $productUuid = 'product-not-found';
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($productUuid)
        ->andReturn(null);
    
    $this->productRepository
        ->shouldNotReceive('delete');
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($productUuid))
        ->toThrow(EcommerceException::class);
});

test('deve lançar exceção quando falha ao deletar', function () {
    // Arrange
    $productUuid = 'product-uuid';
    
    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = $productUuid;
    $product->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($productUuid)
        ->andReturn($product);
    
    $this->productRepository
        ->shouldReceive('delete')
        ->once()
        ->with($productUuid)
        ->andReturn(false); // Falha na deleção
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($productUuid))
        ->toThrow(EcommerceException::class);
});

test('deve verificar existência do produto antes de deletar', function () {
    // Arrange
    $productUuid = 'product-uuid';
    
    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = $productUuid;
    $product->name = 'Produto Existente';
    $product->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Expectativas - findByUuid deve ser chamado ANTES de delete
    $callOrder = [];
    
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($productUuid)
        ->andReturnUsing(function () use (&$callOrder, $product) {
            $callOrder[] = 'findByUuid';
            return $product;
        });
    
    $this->productRepository
        ->shouldReceive('delete')
        ->once()
        ->with($productUuid)
        ->andReturnUsing(function () use (&$callOrder) {
            $callOrder[] = 'delete';
            return true;
        });
    
    // Act
    $this->useCase->execute($productUuid);
    
    // Assert
    expect($callOrder)->toBe(['findByUuid', 'delete']);
});

test('deve retornar true quando produto é deletado com sucesso', function () {
    // Arrange
    $productUuid = 'valid-product-uuid';
    
    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = $productUuid;
    $product->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->andReturn($product);
    
    $this->productRepository
        ->shouldReceive('delete')
        ->andReturn(true);
    
    // Act
    $result = $this->useCase->execute($productUuid);
    
    // Assert
    expect($result)->toBe(true)
        ->and($result)->toBeBool();
});
