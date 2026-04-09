<?php

use App\Application\CQRS\Queries\Product\SearchProductsQuery;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category;
use App\Infrastructure\Persistence\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
    $this->query = new SearchProductsQuery($this->productRepository);
});

afterEach(function () {
    Mockery::close();
});

test('deve buscar produtos por termo com sucesso', function () {
    $searchTerm = 'notebook';

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
    $product1->name = 'Notebook Dell';
    $product1->slug = 'notebook-dell';
    $product1->description = 'Notebook Dell Inspiron';
    $product1->price = 350000;
    $product1->stock = 10;
    $product1->is_active = true;
    $product1->category = $category;
    $product1->created_at = now();
    $product1->updated_at = now();
    $product1->allows()->isAvailable()->andReturn(true);

    $product2 = Mockery::mock(Product::class)->makePartial();
    $product2->uuid = 'product-2';
    $product2->name = 'Notebook HP';
    $product2->slug = 'notebook-hp';
    $product2->description = 'Notebook HP Pavilion';
    $product2->price = 400000;
    $product2->stock = 5;
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
        ->shouldReceive('search')
        ->once()
        ->with($searchTerm, 15)
        ->andReturn($paginator);

    $result = $this->query->execute($searchTerm);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['data', 'pagination'])
        ->and($result['data'])->toHaveCount(2)
        ->and($result['pagination']['total'])->toBe(2);
});

test('deve retornar array vazio quando nenhum produto é encontrado', function () {
    $searchTerm = 'produto inexistente';

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: 15,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('search')
        ->once()
        ->with($searchTerm, 15)
        ->andReturn($paginator);

    $result = $this->query->execute($searchTerm);

    expect($result['data'])->toHaveCount(0)
        ->and($result['pagination']['total'])->toBe(0);
});

test('deve respeitar parâmetro perPage customizado', function () {
    $searchTerm = 'mouse';
    $perPage = 5;

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: $perPage,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('search')
        ->once()
        ->with($searchTerm, $perPage)
        ->andReturn($paginator);

    $result = $this->query->execute($searchTerm, $perPage);

    expect($result['pagination']['per_page'])->toBe($perPage);
});

test('deve retornar estrutura correta de paginação', function () {
    $searchTerm = 'teclado';

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 50,
        perPage: 15,
        currentPage: 2
    );

    $this->productRepository
        ->shouldReceive('search')
        ->once()
        ->andReturn($paginator);

    $result = $this->query->execute($searchTerm);

    expect($result['pagination'])->toHaveKeys([
        'total',
        'per_page',
        'current_page',
        'last_page',
        'from',
        'to'
    ])
    ->and($result['pagination']['total'])->toBe(50)
    ->and($result['pagination']['current_page'])->toBe(2);
});

test('deve usar perPage padrão de 15 quando não fornecido', function () {
    $searchTerm = 'mouse';

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: 15,
        currentPage: 1
    );

    $this->productRepository
        ->shouldReceive('search')
        ->once()
        ->with($searchTerm, 15)
        ->andReturn($paginator);

    $result = $this->query->execute($searchTerm);

    expect($result['pagination']['per_page'])->toBe(15);
});

test('deve retornar produtos com todos os campos necessários', function () {
    $searchTerm = 'notebook';

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
        ->shouldReceive('search')
        ->once()
        ->andReturn($paginator);

    $result = $this->query->execute($searchTerm);

    expect($result['data'])->toHaveCount(1)
        ->and($result['data'][0])->toHaveKeys([
            'uuid',
            'name',
            'slug',
            'description',
            'price',
            'stock',
            'is_available',
            'category'
        ]);
});
