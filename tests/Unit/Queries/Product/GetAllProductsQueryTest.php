<?php

use App\Application\CQRS\Queries\Product\GetAllProductsQuery;
use App\Application\DTOs\Inputs\ProductFiltersInput;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category;
use App\Infrastructure\Persistence\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
    $this->query = new GetAllProductsQuery($this->productRepository);
});

afterEach(function () {
    Mockery::close();
});

test('deve retornar produtos com paginação', function () {
    $filters = new ProductFiltersInput();

    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'category-uuid';
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();

    $product1 = Mockery::mock(Product::class)->makePartial();
    $product1->uuid = 'product-1';
    $product1->name = 'Notebook';
    $product1->slug = 'notebook';
    $product1->description = 'Notebook Dell';
    $product1->price = 350000; // R$ 3500.00 em centavos
    $product1->stock = 10;
    $product1->is_active = true;
    $product1->category = $category;
    $product1->created_at = now();
    $product1->updated_at = now();
    $product1->allows()->isAvailable()->andReturn(true);

    $product2 = Mockery::mock(Product::class)->makePartial();
    $product2->uuid = 'product-2';
    $product2->name = 'Mouse';
    $product2->slug = 'mouse';
    $product2->description = 'Mouse Gamer';
    $product2->price = 15000; // R$ 150.00 em centavos
    $product2->stock = 50;
    $product2->is_active = true;
    $product2->category = $category;
    $product2->created_at = now();
    $product2->updated_at = now();
    $product2->allows()->isAvailable()->andReturn(true);

    $products = collect([$product1, $product2]);
    
    $paginator = new LengthAwarePaginator(
        items: $products,
        total: 2,
        perPage: 15,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('getAllWithFilters')
        ->once()
        ->with(Mockery::type(ProductFiltersInput::class))
        ->andReturn($paginator);

    $result = $this->query->execute($filters);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['data', 'pagination'])
        ->and($result['data'])->toHaveCount(2)
        ->and($result['pagination']['total'])->toBe(2)
        ->and($result['pagination']['per_page'])->toBe(15)
        ->and($result['pagination']['current_page'])->toBe(1);
});

test('deve retornar array vazio quando não há produtos', function () {
    $filters = new ProductFiltersInput();

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: 15,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('getAllWithFilters')
        ->once()
        ->with(Mockery::type(ProductFiltersInput::class))
        ->andReturn($paginator);

    $result = $this->query->execute($filters);

    expect($result['data'])->toHaveCount(0)
        ->and($result['pagination']['total'])->toBe(0);
});

test('deve aplicar filtros corretamente', function () {
    $filters = new ProductFiltersInput(
        search: 'notebook',
        minPrice: 100.00,
        maxPrice: 500.00,
        onlyActive: true,
        onlyInStock: true,
        sortBy: 'price',
        sortOrder: 'asc',
        perPage: 10
    );

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: 10,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('getAllWithFilters')
        ->once()
        ->with(Mockery::on(function ($input) {
            return $input instanceof ProductFiltersInput
                && $input->search === 'notebook'
                && $input->minPrice === 100.00
                && $input->maxPrice === 500.00
                && $input->onlyActive === true
                && $input->onlyInStock === true
                && $input->sortBy === 'price'
                && $input->sortOrder === 'asc'
                && $input->perPage === 10;
        }))
        ->andReturn($paginator);

    $result = $this->query->execute($filters);

    expect($result)->toBeArray();
});

test('deve retornar produtos com estrutura correta de ProductOutput', function () {
    $filters = new ProductFiltersInput();

    $category = Mockery::mock(Category::class)->makePartial();
    $category->uuid = 'category-uuid';
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();

    $product = Mockery::mock(Product::class)->makePartial();
    $product->uuid = 'product-uuid';
    $product->name = 'Notebook';
    $product->slug = 'notebook';
    $product->description = 'Notebook Dell';
    $product->price = 350000;
    $product->stock = 10;
    $product->is_active = true;
    $product->sku = 'NB-001';
    $product->image_url = 'https://example.com/image.jpg';
    $product->category = $category;
    $product->created_at = now();
    $product->updated_at = now();
    $product->allows()->isAvailable()->andReturn(true);

    $paginator = new LengthAwarePaginator(
        items: collect([$product]),
        total: 1,
        perPage: 15,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('getAllWithFilters')
        ->once()
        ->andReturn($paginator);

    $result = $this->query->execute($filters);

    expect($result['data'])->toHaveCount(1)
        ->and($result['data'][0])->toHaveKeys(['uuid', 'name', 'slug', 'price', 'stock', 'category']);
});

test('deve respeitar perPage do filtro', function () {
    $filters = new ProductFiltersInput(perPage: 5);

    $products = collect([]);
    
    $paginator = new LengthAwarePaginator(
        items: $products,
        total: 0,
        perPage: 5,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('getAllWithFilters')
        ->once()
        ->with(Mockery::on(function ($input) {
            return $input->perPage === 5;
        }))
        ->andReturn($paginator);

    $result = $this->query->execute($filters);

    expect($result['pagination']['per_page'])->toBe(5);
});
