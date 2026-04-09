<?php

use App\Application\CQRS\Queries\Product\GetProductsByCategoryQuery;
use App\Application\Exceptions\EcommerceException;
use App\Domain\Interfaces\CategoryRepositoryInterface;
use App\Domain\Interfaces\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Models\Category;
use App\Infrastructure\Persistence\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

uses(Tests\TestCase::class);

beforeEach(function () {
    $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
    $this->categoryRepository = Mockery::mock(CategoryRepositoryInterface::class);
    $this->query = new GetProductsByCategoryQuery(
        $this->productRepository,
        $this->categoryRepository
    );
});

afterEach(function () {
    Mockery::close();
});

test('deve buscar produtos por categoria com sucesso', function () {
    $categoryUuid = 'category-uuid';

    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = $categoryUuid;
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
    $product1->price = 350000;
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
    $product2->price = 15000;
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

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($categoryUuid)
        ->andReturn($category);

    $this->productRepository
        ->shouldReceive('getByCategoryId')
        ->once()
        ->with(1, 15)
        ->andReturn($paginator);

    $result = $this->query->execute($categoryUuid);

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['data', 'pagination'])
        ->and($result['data'])->toHaveCount(2)
        ->and($result['pagination']['total'])->toBe(2);
});

test('deve lançar exceção quando categoria não existe', function () {
    $categoryUuid = 'non-existent-uuid';

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($categoryUuid)
        ->andReturn(null);

    $this->productRepository
        ->shouldNotReceive('getByCategoryId');

    expect(fn() => $this->query->execute($categoryUuid))
        ->toThrow(EcommerceException::class);
});

test('deve retornar array vazio quando categoria não tem produtos', function () {
    $categoryUuid = 'category-uuid';

    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = $categoryUuid;
    $category->name = 'Categoria Vazia';
    $category->slug = 'categoria-vazia';
    $category->description = 'Sem produtos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: 15,
        currentPage: 1
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($categoryUuid)
        ->andReturn($category);

    $this->productRepository
        ->shouldReceive('getByCategoryId')
        ->once()
        ->with(1, 15)
        ->andReturn($paginator);

    $result = $this->query->execute($categoryUuid);

    expect($result['data'])->toHaveCount(0)
        ->and($result['pagination']['total'])->toBe(0);
});

test('deve respeitar parâmetro perPage customizado', function () {
    $categoryUuid = 'category-uuid';
    $perPage = 5;

    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = $categoryUuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: $perPage,
        currentPage: 1
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->andReturn($category);

    $this->productRepository
        ->shouldReceive('getByCategoryId')
        ->once()
        ->with(1, $perPage)
        ->andReturn($paginator);

    $result = $this->query->execute($categoryUuid, $perPage);

    expect($result['pagination']['per_page'])->toBe($perPage);
});

test('deve usar perPage padrão de 15 quando não fornecido', function () {
    $categoryUuid = 'category-uuid';

    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = $categoryUuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 0,
        perPage: 15,
        currentPage: 1
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->andReturn($category);

    $this->productRepository
        ->shouldReceive('getByCategoryId')
        ->once()
        ->with(1, 15)
        ->andReturn($paginator);

    $result = $this->query->execute($categoryUuid);

    expect($result['pagination']['per_page'])->toBe(15);
});

test('deve retornar estrutura correta de paginação', function () {
    $categoryUuid = 'category-uuid';

    $category = Mockery::mock(Category::class)->makePartial();
    $category->id = 1;
    $category->uuid = $categoryUuid;
    $category->name = 'Eletrônicos';
    $category->slug = 'eletronicos';
    $category->description = 'Produtos eletrônicos';
    $category->is_active = true;
    $category->created_at = now();
    $category->updated_at = now();

    $paginator = new LengthAwarePaginator(
        items: collect([]),
        total: 50,
        perPage: 15,
        currentPage: 2
    );

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->andReturn($category);

    $this->productRepository
        ->shouldReceive('getByCategoryId')
        ->once()
        ->andReturn($paginator);

    $result = $this->query->execute($categoryUuid);

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

test('deve verificar categoria antes de buscar produtos', function () {
    $categoryUuid = 'category-uuid';

    $this->categoryRepository
        ->shouldReceive('findByUuid')
        ->once()
        ->with($categoryUuid)
        ->andReturn(null);

    $this->productRepository
        ->shouldNotReceive('getByCategoryId');

    expect(fn() => $this->query->execute($categoryUuid))
        ->toThrow(EcommerceException::class);
});
