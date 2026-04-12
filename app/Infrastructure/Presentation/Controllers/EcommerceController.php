<?php

namespace App\Infrastructure\Presentation\Controllers;

use App\Application\CQRS\Queries\Category\GetActiveCategoriesQuery;
use App\Application\CQRS\Queries\Category\GetAllCategoriesQuery;
use App\Application\CQRS\Queries\Category\GetCategoryByIdQuery;
use App\Application\CQRS\Queries\Product\GetActiveProductsQuery;
use App\Application\CQRS\Queries\Product\GetAllProductsQuery;
use App\Application\CQRS\Queries\Product\GetProductByUuidQuery;
use App\Application\CQRS\Queries\Product\GetProductsByCategoryQuery;
use App\Application\CQRS\Queries\Product\SearchProductsByPriceQuery;
use App\Application\CQRS\Queries\Product\SearchProductsQuery;
use App\Application\DTOs\Inputs\CreateCategoryInput;
use App\Application\DTOs\Inputs\CreateProductInput;
use App\Application\DTOs\Inputs\ProductFiltersInput;
use App\Application\DTOs\Inputs\UpdateCategoryInput;
use App\Application\DTOs\Inputs\UpdateProductInput;
use App\Application\UseCases\Category\CreateCategoryUseCase;
use App\Application\UseCases\Category\DeleteCategoryUseCase;
use App\Application\UseCases\Category\UpdateCategoryUseCase;
use App\Application\UseCases\Product\CreateProductUseCase;
use App\Application\UseCases\Product\DeleteProductUseCase;
use App\Application\UseCases\Product\UpdateProductUseCase;
use App\Http\Requests\Category\CreateCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\ListProductsRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Controller de E-commerce
 * 
 * Responsável por:
 * - CRUD de Produtos
 * - CRUD de Categorias
 * - Buscas e Listagens
 */
class EcommerceController extends BaseController
{
    // ========================================
    // PRODUTOS
    // ========================================

    /**
     * Listar produtos com filtros
     * 
     * GET /api/v1/products
     * 
     * Query params:
     * - search: string (busca em nome/descrição)
     * - categories[]: array de UUIDs
     * - min_price: float (obrigatório se max_price informado)
     * - max_price: float (obrigatório se min_price informado, deve ser >= min_price)
     * - only_active: boolean
     * - only_in_stock: boolean
     * - sort_by: created_at|name|price
     * - sort_order: asc|desc
     * - per_page: int (max: 100)
     */
    public function listProducts(
        ListProductsRequest $request,
        GetAllProductsQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($request, $query) {
            $filters = ProductFiltersInput::fromArray($request->validated());
            $result = $query->execute($filters);

            return $this->successResponse(data: $result);
        });
    }

    /**
     * Buscar produto por UUID
     * 
     * GET /api/v1/products/{uuid}
     */
    public function showProduct(
        string $uuid,
        GetProductByUuidQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($uuid, $query) {
            $product = $query->execute($uuid);

            if (!$product) {
                return $this->errorResponse(
                    message: 'Produto não encontrado',
                    code: 404
                );
            }

            return $this->successResponse(data: $product->toArray());
        });
    }

    /**
     * Criar novo produto
     * 
     * POST /api/v1/products
     */
    public function createProduct(
        CreateProductRequest $request,
        CreateProductUseCase $useCase
    ): JsonResponse {
        return $this->execute(function () use ($request, $useCase) {
            $input = CreateProductInput::fromArray($request->validated());
            $product = $useCase->execute($input);

            return $this->successResponse(
                data: $product->toArray(),
                message: 'Produto criado com sucesso',
                code: 201
            );
        });
    }

    /**
     * Atualizar produto
     * 
     * PUT /api/v1/products/{uuid}
     */
    public function updateProduct(
        string $uuid,
        UpdateProductRequest $request,
        UpdateProductUseCase $useCase
    ): JsonResponse {
        return $this->execute(function () use ($uuid, $request, $useCase) {
            $input = UpdateProductInput::fromArray($request->validated());
            $product = $useCase->execute($uuid, $input);

            return $this->successResponse(
                data: $product->toArray(),
                message: 'Produto atualizado com sucesso'
            );
        });
    }

    /**
     * Deletar produto (soft delete)
     * 
     * DELETE /api/v1/products/{uuid}
     */
    public function deleteProduct(
        string $uuid,
        DeleteProductUseCase $useCase
    ): JsonResponse {
        return $this->execute(function () use ($uuid, $useCase) {
            $useCase->execute($uuid);

            return $this->successResponse(
                message: 'Produto deletado com sucesso'
            );
        });
    }

    /**
     * Buscar produtos por categoria
     * 
     * GET /api/v1/products/category/{categoryUuid}
     */
    public function productsByCategory(
        string $categoryUuid,
        Request $request,
        GetProductsByCategoryQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($categoryUuid, $request, $query) {
            $perPage = (int) $request->get('per_page', 15);
            $result = $query->execute($categoryUuid, $perPage);

            return $this->successResponse(data: $result);
        });
    }

    /**
     * Buscar produtos ativos
     * 
     * GET /api/v1/products/active
     */
    public function activeProducts(
        Request $request,
        GetActiveProductsQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($request, $query) {
            $perPage = (int) $request->get('per_page', 15);
            $result = $query->execute($perPage);

            return $this->successResponse(data: $result);
        });
    }

    /**
     * Buscar produtos por termo
     * 
     * GET /api/v1/products/search?q=termo
     */
    public function searchProducts(
        Request $request,
        SearchProductsQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($request, $query) {
            $searchTerm = $request->get('q', '');
            $perPage = (int) $request->get('per_page', 15);

            if (empty($searchTerm)) {
                return $this->errorResponse(
                    message: 'Termo de busca não fornecido',
                    code: 422
                );
            }

            $result = $query->execute($searchTerm, $perPage);

            return $this->successResponse(data: $result);
        });
    }

    /**
     * Buscar produtos por faixa de preço
     * 
     * GET /api/v1/products/price-range?min=10.00&max=100.00
     */
    public function productsByPriceRange(
        Request $request,
        SearchProductsByPriceQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($request, $query) {
            $validator = Validator::make($request->all(), [
                'min' => 'required|numeric|min:0',
                'max' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    message: 'Erro de validação',
                    code: 422,
                    errors: $validator->errors()->toArray()
                );
            }

            $minPrice = (float) $request->get('min');
            $maxPrice = (float) $request->get('max');
            $perPage = (int) $request->get('per_page', 15);

            $result = $query->execute($minPrice, $maxPrice, $perPage);

            return $this->successResponse(data: $result);
        });
    }

    // ========================================
    // CATEGORIAS
    // ========================================

    /**
     * Listar todas as categorias
     * 
     * GET /api/v1/categories
     */
    public function listCategories(GetAllCategoriesQuery $query): JsonResponse
    {
        return $this->execute(function () use ($query) {
            $result = $query->execute();

            return $this->successResponse(data: $result);
        });
    }

    /**
     * Listar categorias ativas
     * 
     * GET /api/v1/categories/active
     */
    public function activeCategories(GetActiveCategoriesQuery $query): JsonResponse
    {
        return $this->execute(function () use ($query) {
            $result = $query->execute();

            return $this->successResponse(data: $result);
        });
    }

    /**
     * Buscar categoria por UUID
     * 
     * GET /api/v1/categories/{uuid}
     */
    public function showCategory(
        string $uuid,
        GetCategoryByIdQuery $query
    ): JsonResponse {
        return $this->execute(function () use ($uuid, $query) {
            $category = $query->execute($uuid);

            if (!$category) {
                return $this->errorResponse(
                    message: 'Categoria não encontrada',
                    code: 404
                );
            }

            return $this->successResponse(data: $category->toArray());
        });
    }

    /**
     * Criar nova categoria
     * 
     * POST /api/v1/categories
     */
    public function createCategory(
        CreateCategoryRequest $request,
        CreateCategoryUseCase $useCase
    ): JsonResponse {
        return $this->execute(function () use ($request, $useCase) {
            $input = CreateCategoryInput::fromArray($request->validated());
            $category = $useCase->execute($input);

            return $this->successResponse(
                data: $category->toArray(),
                message: 'Categoria criada com sucesso',
                code: 201
            );
        });
    }

    /**
     * Atualizar categoria
     * 
     * PUT /api/v1/categories/{uuid}
     */
    public function updateCategory(
        string $uuid,
        UpdateCategoryRequest $request,
        UpdateCategoryUseCase $useCase
    ): JsonResponse {
        return $this->execute(function () use ($uuid, $request, $useCase) {
            $input = UpdateCategoryInput::fromArray($request->validated());
            $category = $useCase->execute($uuid, $input);

            return $this->successResponse(
                data: $category->toArray(),
                message: 'Categoria atualizada com sucesso'
            );
        });
    }

    /**
     * Deletar categoria (soft delete)
     * 
     * DELETE /api/v1/categories/{uuid}
     */
    public function deleteCategory(
        string $uuid,
        DeleteCategoryUseCase $useCase
    ): JsonResponse {
        return $this->execute(function () use ($uuid, $useCase) {
            $useCase->execute($uuid);

            return $this->successResponse(
                message: 'Categoria deletada com sucesso'
            );
        });
    }
}
