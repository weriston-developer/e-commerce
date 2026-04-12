# E-Commerce API - Copilot Instructions

## Architecture Overview

This is a **Laravel 13** e-commerce API implementing **Clean Architecture**, **DDD**, and **CQRS** patterns with strict separation of concerns:

```
Presentation → Application → Domain → Infrastructure
```

### Key Architectural Rules

1. **CQRS Separation**: Write operations use `UseCases` (in `app/Application/UseCases/`), read operations use `Queries` (in `app/Application/CQRS/Queries/`)
2. **Price Storage**: ALL prices are stored as **cents (integer)** in database, converted via `MoneyVO` in application layer
3. **Repository Pattern**: Domain interfaces in `app/Domain/Interfaces/`, implementations in `app/Infrastructure/Persistence/Repositories/`
4. **DTOs Everywhere**: Input DTOs for requests, Output DTOs for responses - never expose Eloquent models directly
5. **Readonly Classes**: All UseCases, Queries, and DTOs use `readonly` modifier

## Critical Conventions

### Use Case Pattern (Write Operations)
```php
readonly class CreateProductUseCase
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {}

    public function execute(CreateProductInput $input): ProductOutput
    {
        // Business logic here
        // Throw EcommerceException for domain errors
    }
}
```

### Query Pattern (Read Operations)
```php
readonly class GetAllProductsQuery
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {}

    public function execute(ProductFiltersInput $filters): array
    {
        // Returns ['data' => [...], 'pagination' => [...]]
    }
}
```

### Money Handling (CRITICAL)
- **Database**: Store as cents (integer) - e.g., R$ 35.50 = 3550
- **Input**: Convert to cents: `(new MoneyVO($input->price))->toCents()`
- **Output**: Convert from cents: `new MoneyVO($product->price / 100)`
- **Never** store decimals directly in database

### DTOs Structure
```php
readonly class ProductOutput
{
    public static function fromModel(Product $product): self {
        $money = new MoneyVO($product->price / 100);
        return new self(
            price: $money->toFloat(),
            priceFormatted: $money->format(), // "R$ 3.500,00"
            // ...
        );
    }
}
```

### Error Handling
- Domain errors: Throw `new EcommerceException(new ProductNotFoundError($uuid))`
- All error classes in `app/Application/Errors/`
- Custom exceptions in `app/Application/Exceptions/`

### Authorization
- Gates defined in `AuthServiceProvider`: `manage-products`, `manage-categories`, `delete-users`
- Roles: `UserRole::ADMIN`, `UserRole::USER` (enum in `app/Domain/Enums/`)
- Only ADMIN can write operations on products/categories

## Development Workflow

### Running Commands (Docker/Sail)
```bash
# Windows PowerShell (preferred in this workspace)
docker compose exec laravel.test <command>

# Or using Sail alias
./vendor/bin/sail artisan <command>
```

### Testing (Pest PHP - 57 tests, 100% mocked)
```bash
# All tests
docker compose exec laravel.test ./vendor/bin/pest

# Specific domain
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Product/
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Category/
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Auth/
docker compose exec laravel.test ./vendor/bin/pest tests/Unit/Queries/
```

### Test Pattern (100% Mock, No DB)
```php
beforeEach(function () {
    $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
    $this->app->instance(ProductRepositoryInterface::class, $this->productRepository);
    $this->useCase = app(CreateProductUseCase::class);
});

test('deve criar produto com sucesso', function () {
    // Always mock repositories, never hit database
    $this->categoryRepository->shouldReceive('findByUuid')->andReturn($mockCategory);
});
```

### Database Migrations
```bash
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test php artisan db:seed
```

## Project-Specific Patterns

### Controller Organization
- Single mega-controller: `EcommerceController` handles all product/category CRUD
- `AuthController` handles JWT authentication
- Controllers in `app/Infrastructure/Presentation/Controllers/`
- Form Requests in `app/Http/Requests/`

### Soft Deletes
- Products and Categories use `SoftDeletes` trait
- Always include `->whereNull('deleted_at')` in queries or use `->withTrashed()` explicitly

### UUID Usage
- All entities use UUID as public identifier (NOT auto-increment ID)
- Always search by UUID in public APIs: `findByUuid($uuid)`
- Internal relations use integer IDs

### Repository Filtering
- Complex filters use dedicated Input DTOs (e.g., `ProductFiltersInput`)
- Repositories return `LengthAwarePaginator` for list operations
- Standard pagination: `per_page` param (default: 15)

### JWT Authentication
- Library: `tymon/jwt-auth`
- Token in header: `Authorization: Bearer {token}`
- Refresh endpoint: `POST /api/v1/auth/refresh`
- Token expiry: 3600 seconds (configurable in `config/jwt.php`)

## When Creating New Features

1. **Domain Entity** → `app/Domain/Entities/`
2. **Repository Interface** → `app/Domain/Interfaces/`
3. **Eloquent Model** → `app/Infrastructure/Persistence/Models/`
4. **Repository Implementation** → `app/Infrastructure/Persistence/Repositories/`
5. **Input DTO** → `app/Application/DTOs/Inputs/`
6. **Output DTO** → `app/Application/DTOs/Outputs/`
7. **Use Case** (write) → `app/Application/UseCases/{Feature}/`
8. **Query** (read) → `app/Application/CQRS/Queries/{Feature}/`
9. **Form Request** → `app/Http/Requests/{Feature}/`
10. **Controller Method** → Add to existing controller or create new
11. **Route** → `routes/api.php`
12. **Tests** → `tests/Unit/{Feature}/` (use Mockery, 100% mocked)

## Common Gotchas

- ❌ Don't return Eloquent models from controllers - always use Output DTOs
- ❌ Don't store prices as decimals - always use cents (integer)
- ❌ Don't query database in tests - always mock repositories
- ❌ Don't put business logic in controllers - use UseCases/Queries
- ❌ Don't expose internal IDs - always use UUIDs in public APIs
- ✅ Always use `readonly` for UseCases, Queries, DTOs
- ✅ Always inject repository interfaces, not implementations
- ✅ Always validate inputs with Form Requests before reaching UseCases
- ✅ Always use Gates for authorization checks
