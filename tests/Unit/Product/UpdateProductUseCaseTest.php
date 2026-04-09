<?php

use App\Application\DTOs\Inputs\UpdateProductInput;
use App\Application\Errors\CategoryNotFoundError;
use App\Application\Errors\ProductNotFoundError;
use App\Application\Errors\ProductSlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Application\UseCases\Product\UpdateProductUseCase;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Domain\ValueObjects\MoneyVO;
use App\Infrastructure\Persistence\Models\Category;
use App\Infrastructure\Persistence\Models\Product;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
    $this->categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
    
    $this->app->instance(ProductRepositoryInterface::class, $this->productRepository);
    $this->app->instance(CategoryRepositoryInterface::class, $this->categoryRepository);
    
    $this->useCase = app(UpdateProductUseCase::class);
});

afterEach(function () {
    Mockery::close();
});

test('deve atualizar produto com sucesso', function () {
    // Arrange
    $productUuid = 'product-uuid-123';
    $newName = 'Notebook Gamer Atualizado';
    $newPrice = 5000.00;
    
    $input = new UpdateProductInput(
        name: $newName,
        price: new MoneyVO($newPrice),
        description: 'Descrição atualizada',
        imageUrl: 'https://example.com/new-image.jpg',
        categoryUuid: null,
        stock: 15,
        isActive: true
    );
    
    // Mock do produto existente
    $existingProduct = Mockery::mock(Product::class)->makePartial();
    $existingProduct->id = 1;
    $existingProduct->uuid = $productUuid;
    $existingProduct->name = 'Notebook Gamer';
    $existingProduct->slug = 'notebook-gamer';
    $existingProduct->price = 450000; // MoneyVO em centavos
    $existingProduct->description = 'Descrição antiga';
    $existingProduct->category_id = 1;
    $existingProduct->stock = 10;
    $existingProduct->is_active = true;
    $existingProduct->sku = 'NB-001';
    $existingProduct->image_url = 'https://old-image.jpg';
    $existingProduct->created_at = now();
    $existingProduct->updated_at = now();
    $existingProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Mock do produto atualizado
    $updatedProduct = Mockery::mock(Product::class)->makePartial();
    $updatedProduct->uuid = $productUuid;
    $updatedProduct->name = $newName;
    $updatedProduct->slug = 'notebook-gamer-atualizado';
    $updatedProduct->price = 500000; // MoneyVO em centavos
    $updatedProduct->description = 'Descrição atualizada';
    $updatedProduct->stock = 15;
    $updatedProduct->image_url = 'https://example.com/new-image.jpg';
    
    // Mock da categoria para o relacionamento
    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'cat-uuid';
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $updatedProduct->category = $category;
    $updatedProduct->created_at = now();
    $updatedProduct->updated_at = now();
    $updatedProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    $updatedProduct->allows()->isAvailable()->andReturn(true);
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($productUuid)
        ->andReturn($existingProduct);
    
    $this->productRepository
        ->shouldReceive('existsBySlug')
        ->once()
        ->with('notebook-gamer-atualizado', 1)
        ->andReturn(false);
    
    $this->productRepository
        ->shouldReceive('update')
        ->once()
        ->with($productUuid, Mockery::on(function ($data) {
            return isset($data['name'])
                && $data['slug'] === 'notebook-gamer-atualizado'
                && isset($data['price'])
                && isset($data['stock']);
        }))
        ->andReturn($updatedProduct);
    
    // Act
    $result = $this->useCase->execute($productUuid, $input);
    
    // Assert
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe($newName)
        ->and($result->price)->toBe($newPrice)
        ->and($result->slug)->toBe('notebook-gamer-atualizado');
});

test('deve lançar exceção quando produto não existe', function () {
    // Arrange
    $productUuid = 'product-not-found';
    $input = new UpdateProductInput(
        name: 'Novo Nome',
        price: null,
        description: null,
        imageUrl: null,
        categoryUuid: null,
        stock: null,
        isActive: null
    );
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($productUuid)
        ->andReturn(null);
    
    $this->productRepository
        ->shouldNotReceive('update');
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($productUuid, $input))
        ->toThrow(EcommerceException::class);
});

test('deve atualizar apenas campos fornecidos', function () {
    // Arrange
    $productUuid = 'product-uuid';
    
    // Input com apenas preço
    $input = new UpdateProductInput(
        name: null,
        price: new MoneyVO(999.99),
        description: null,
        imageUrl: null,
        categoryUuid: null,
        stock: null,
        isActive: null
    );
    
    $existingProduct = Mockery::mock(Product::class)->makePartial();
    $existingProduct->id = 1;
    $existingProduct->uuid = $productUuid;
    $existingProduct->name = 'Produto Original';
    $existingProduct->slug = 'produto-original';
    $existingProduct->price = 50000; // MoneyVO em centavos
    $existingProduct->category_id = 1;
    $existingProduct->stock = 5; // Adiciona stock
    $existingProduct->is_active = true;
    $existingProduct->sku = 'PROD-001';
    $existingProduct->description = 'Descrição original';
    $existingProduct->image_url = null;
    $existingProduct->created_at = now();
    $existingProduct->updated_at = now();
    $existingProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $updatedProduct = Mockery::mock(Product::class)->makePartial();
    $updatedProduct->uuid = $productUuid;
    $updatedProduct->name = 'Produto Original';
    $updatedProduct->slug = 'produto-original';
    $updatedProduct->price = 99999; // MoneyVO em centavos
    $updatedProduct->stock = 5;
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'cat-uuid';
    $category->name = 'Categoria';
    $category->slug = 'categoria';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $updatedProduct->category = $category;
    $updatedProduct->created_at = now();
    $updatedProduct->updated_at = now();
    $updatedProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    $updatedProduct->allows()->isAvailable()->andReturn(true);
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->andReturn($existingProduct);
    
    $this->productRepository
        ->shouldReceive('update')
        ->once()
        ->with($productUuid, Mockery::on(function ($data) {
            return isset($data['price'])
                && $data['name'] === 'Produto Original'; // Nome não mudou
        }))
        ->andReturn($updatedProduct);
    
    // Act
    $result = $this->useCase->execute($productUuid, $input);
    
    // Assert
    expect($result->price)->toBe(999.99);
});

test('deve lançar exceção quando novo slug já existe', function () {
    // Arrange
    $productUuid = 'product-uuid';
    $input = new UpdateProductInput(
        name: 'Mouse Gamer', // Slug vai ser 'mouse-gamer'
        price: null,
        description: null,
        imageUrl: null,
        categoryUuid: null,
        stock: null,
        isActive: null
    );
    
    $existingProduct = Mockery::mock(Product::class)->makePartial();
    $existingProduct->id = 1;
    $existingProduct->uuid = $productUuid;
    $existingProduct->name = 'Teclado Gamer';
    $existingProduct->slug = 'teclado-gamer';
    $existingProduct->price = 30000;
    $existingProduct->category_id = 1;
    $existingProduct->stock = 10; // Adiciona stock
    $existingProduct->is_active = true;
    $existingProduct->sku = 'TEC-001';
    $existingProduct->description = 'Teclado mecânico';
    $existingProduct->image_url = null;
    $existingProduct->created_at = now();
    $existingProduct->updated_at = now();
    $existingProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->andReturn($existingProduct);
    
    $this->productRepository
        ->shouldReceive('existsBySlug')
        ->once()
        ->with('mouse-gamer', 1)
        ->andReturn(true); // Slug já existe em outro produto
    
    $this->productRepository
        ->shouldNotReceive('update');
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($productUuid, $input))
        ->toThrow(EcommerceException::class);
});

test('deve atualizar categoria quando fornecida', function () {
    // Arrange
    $productUuid = 'product-uuid';
    $newCategoryUuid = 'new-category-uuid';
    
    $input = new UpdateProductInput(
        name: null,
        price: null,
        description: null,
        imageUrl: null,
        categoryUuid: $newCategoryUuid,
        stock: null,
        isActive: null
    );
    
    $existingProduct = Mockery::mock(Product::class)->makePartial();
    $existingProduct->id = 1;
    $existingProduct->uuid = $productUuid;
    $existingProduct->name = 'Produto';
    $existingProduct->slug = 'produto';
    $existingProduct->price = 10000;
    $existingProduct->category_id = 1;
    $existingProduct->stock = 3; // Adiciona stock
    $existingProduct->is_active = true;
    $existingProduct->sku = 'PROD-123';
    $existingProduct->description = 'Produto teste';
    $existingProduct->image_url = null;
    $existingProduct->created_at = now();
    $existingProduct->updated_at = now();
    $existingProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $newCategory = Mockery::mock(Category::class)->makePartial();
    $newCategory->id = 2;
    $newCategory->uuid = $newCategoryUuid;
    $newCategory->name = 'Nova Categoria';
    $newCategory->slug = 'nova-categoria';
    $newCategory->is_active = true;
    $newCategory->created_at = now();
    $newCategory->updated_at = now();
    $newCategory->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $updatedProduct = Mockery::mock(Product::class)->makePartial();
    $updatedProduct->uuid = $productUuid;
    $updatedProduct->name = 'Produto';
    $updatedProduct->slug = 'produto';
    $updatedProduct->category_id = 2;
    $updatedProduct->stock = 3;
    $updatedProduct->category = $newCategory;
    $updatedProduct->created_at = now();
    $updatedProduct->updated_at = now();
    $updatedProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    $updatedProduct->allows()->isAvailable()->andReturn(true);
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->andReturn($existingProduct);
    
    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($newCategoryUuid)
        ->andReturn($newCategory);
    
    $this->productRepository
        ->shouldReceive('update')
        ->once()
        ->with($productUuid, Mockery::on(function ($data) {
            return $data['category_id'] === 2;
        }))
        ->andReturn($updatedProduct);
    
    // Act
    $result = $this->useCase->execute($productUuid, $input);
    
    // Assert
    expect($result)->not->toBeNull();
});

test('deve lançar exceção quando nova categoria não existe', function () {
    // Arrange
    $productUuid = 'product-uuid';
    $invalidCategoryUuid = 'invalid-category';
    
    $input = new UpdateProductInput(
        name: null,
        price: null,
        description: null,
        imageUrl: null,
        categoryUuid: $invalidCategoryUuid,
        stock: null,
        isActive: null
    );
    
    $existingProduct = Mockery::mock(Product::class)->makePartial();
    $existingProduct->id = 1;
    $existingProduct->uuid = $productUuid;
    $existingProduct->name = 'Produto';
    $existingProduct->slug = 'produto';
    $existingProduct->price = 10000;
    $existingProduct->category_id = 1;
    $existingProduct->stock = 5; // Adiciona stock
    $existingProduct->is_active = true;
    $existingProduct->sku = 'PROD-999';
    $existingProduct->description = 'Descrição';
    $existingProduct->image_url = null;
    $existingProduct->created_at = now();
    $existingProduct->updated_at = now();
    $existingProduct->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Expectativas
    $this->productRepository
        ->shouldReceive('findByUuid')
        ->andReturn($existingProduct);
    
    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($invalidCategoryUuid)
        ->andReturn(null);
    
    $this->productRepository
        ->shouldNotReceive('update');
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($productUuid, $input))
        ->toThrow(EcommerceException::class);
});
