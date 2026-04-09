<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ApiFormRequest;

/**
 * Request para listagem de produtos com filtros
 */
class ListProductsRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true; // Rota pública
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:200',
            'categories' => 'nullable|array',
            'categories.*' => 'uuid|exists:categories,uuid',
            'min_price' => 'nullable|numeric|min:0|required_with:max_price',
            'max_price' => 'nullable|numeric|min:0|required_with:min_price|gte:min_price',
            'only_active' => 'nullable|boolean',
            'only_in_stock' => 'nullable|boolean',
            'sort_by' => 'nullable|in:created_at,name,price',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'min_price.required_with' => 'Quando informar o preço mínimo, deve informar também o preço máximo',
            'max_price.required_with' => 'Quando informar o preço máximo, deve informar também o preço mínimo',
            'max_price.gte' => 'O preço máximo deve ser maior ou igual ao preço mínimo',
            'categories.*.uuid' => 'UUID de categoria inválido',
            'categories.*.exists' => 'Categoria não encontrada',
            'sort_by.in' => 'Ordenação inválida. Use: created_at, name ou price',
            'sort_order.in' => 'Ordem inválida. Use: asc ou desc',
            'per_page.max' => 'Máximo de 100 itens por página',
        ];
    }
}
