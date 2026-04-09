<?php

use App\Application\DTOs\Inputs\CreateProductInput;
use App\Application\Errors\CategoryNotExistsError;
use App\Application\Errors\ProductSlugAlreadyExistsError;
use App\Application\Exceptions\EcommerceException;
use App\Application\UseCases\Product\CreateProductUseCase;
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
    
    $this->useCase = app(CreateProductUseCase::class);
});

afterEach(function () {
    Mockery::close();
});

test('deve criar produto com sucesso', function () {
    // Arrange
    $categoryUuid = 'category-uuid-123';
    $name = 'Notebook Gamer';
    $price = 4500.00;
    $description = 'Notebook potente para jogos';
    $imageUrl = 'https://example.com/image.jpg';
    $stock = 10;
    $sku = 'NB-GAMER-001';
    $isActive = true;
    
    $input = new CreateProductInput(
        name: $name,
        price: new MoneyVO($price),
        categoryId: $categoryUuid,
        description: $description,
        imageUrl: $imageUrl,
        stock: $stock,
        sku: $sku,
        isActive: $isActive
    );
    
    // Mock da categoria
    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = $categoryUuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Mock do produto criado
    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = 'product-uuid-123';
    $product->name = $name;
    $product->slug = 'notebook-gamer';
    $product->price = 450000; // MoneyVO internamente usa centavos
    $product->description = $description;
    $product->image_url = $imageUrl;
    $product->stock = $stock;
    $product->sku = $sku;
    $product->is_active = $isActive;
    $product->category_id = 1;
    $product->category = $category; // Relacionamento
    $product->created_at = now();
    $product->updated_at = now();
    $product->allows()->setAttribute(Mockery::any(), Mockery::any());
    $product->allows()->isAvailable()->andReturn(true);
    
    // Expectativas
    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($categoryUuid)
        ->andReturn($category);
    
    $this->productRepository
        ->shouldReceive('existsBySlug')
        ->once()
        ->with('notebook-gamer')
        ->andReturn(false);
    
    $this->productRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) use ($name) {
            return isset($data['name']) 
                && $data['name'] === $name
                && $data['slug'] === 'notebook-gamer'
                && isset($data['price'])
                && isset($data['category_id']);
        }))
        ->andReturn($product);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe($name)
        ->and($result->slug)->toBe('notebook-gamer')
        ->and($result->price)->toBe($price)
        ->and($result->stock)->toBe($stock);
});

test('deve lançar exceção quando categoria não existe', function () {
    // Arrange
    $categoryUuid = 'category-not-found';
    $input = new CreateProductInput(
        name: 'Produto Teste',
        price: new MoneyVO(100.00),
        categoryId: $categoryUuid,
        description: 'Descrição',
        imageUrl: null,
        stock: 5,
        sku: 'SKU-001',
        isActive: true
    );
    
    // Expectativas
    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($categoryUuid)
        ->andReturn(null);
    
    $this->productRepository
        ->shouldNotReceive('existsBySlug');
    
    $this->productRepository
        ->shouldNotReceive('create');
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($input))
        ->toThrow(EcommerceException::class);
});

test('deve lançar exceção quando slug já existe', function () {
    // Arrange
    $categoryUuid = 'category-uuid';
    $input = new CreateProductInput(
        name: 'Mouse Gamer',
        price: new MoneyVO(150.00),
        categoryId: $categoryUuid,
        description: 'Mouse RGB',
        imageUrl: null,
        stock: 20,
        sku: 'MOUSE-001',
        isActive: true
    );
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = $categoryUuid;
    $category->name = 'Periféricos';
    $category->slug = 'perifericos';
    $category->description = null;
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    // Expectativas
    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($categoryUuid)
        ->andReturn($category);
    
    $this->productRepository
        ->shouldReceive('existsBySlug')
        ->once()
        ->with('mouse-gamer')
        ->andReturn(true);
    
    $this->productRepository
        ->shouldNotReceive('create');
    
    // Act & Assert
    expect(fn() => $this->useCase->execute($input))
        ->toThrow(EcommerceException::class);
});

test('deve gerar slug automaticamente a partir do nome', function () {
    // Arrange
    $categoryUuid = 'category-uuid';
    $productName = 'Teclado Mecânico RGB com LED';
    
    $input = new CreateProductInput(
        name: $productName,
        price: new MoneyVO(350.00),
        categoryId: $categoryUuid,
        description: 'Teclado gamer',
        imageUrl: null,
        stock: 15,
        sku: 'TEC-001',
        isActive: true
    );
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = 'category-uuid-teclado';
    $category->name = 'Periféricos';
    $category->slug = 'perifericos';
    $category->description = 'Periféricos de computador';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = 'prod-uuid';
    $product->name = $productName;
    $product->slug = 'teclado-mecanico-rgb-com-led';
    $product->price = 350.00;
    $product->stock = 15; // Adiciona stock
    $product->category = $category; // Relacionamento
    $product->created_at = now();
    $product->updated_at = now();
    $product->allows()->setAttribute(Mockery::any(), Mockery::any());
    $product->allows()->isAvailable()->andReturn(true);
    
    // Expectativas
    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->andReturn($category);
    
    $this->productRepository
        ->shouldReceive('existsBySlug')
        ->once()
        ->with('teclado-mecanico-rgb-com-led')
        ->andReturn(false);
    
    $this->productRepository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['slug'] === 'teclado-mecanico-rgb-com-led';
        }))
        ->andReturn($product);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result->slug)->toBe('teclado-mecanico-rgb-com-led');
});

test('deve criar produto com campos opcionais nulos', function () {
    // Arrange
    $categoryUuid = 'category-uuid';
    $input = new CreateProductInput(
        name: 'Produto Simples',
        price: new MoneyVO(50.00),
        categoryId: $categoryUuid,
        description: null,
        imageUrl: null,
        stock: 0,
        sku: null,
        isActive: true
    );
    
    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = 'category-uuid-simple';
    $category->name = 'Diversos';
    $category->slug = 'diversos';
    $category->description = null;
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();
    $category->allows()->setAttribute(Mockery::any(), Mockery::any());
    
    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = 'simple-prod-uuid';
    $product->name = 'Produto Simples';
    $product->slug = 'produto-simples';
    $product->price = 50.00;
    $product->description = null;
    $product->image_url = null;
    $product->stock = 0;
    $product->sku = null;
    $product->category = $category; // Relacionamento
    $product->created_at = now();
    $product->updated_at = now();
    $product->allows()->setAttribute(Mockery::any(), Mockery::any());
    $product->allows()->isAvailable()->andReturn(false);
    
    // Expectativas
    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->andReturn($category);
    
    $this->productRepository
        ->shouldReceive('existsBySlug')
        ->andReturn(false);
    
    $this->productRepository
        ->shouldReceive('create')
        ->once()
        ->andReturn($product);
    
    // Act
    $result = $this->useCase->execute($input);
    
    // Assert
    expect($result)->not->toBeNull()
        ->and($result->name)->toBe('Produto Simples');
});
