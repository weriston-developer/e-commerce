<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class CreateProductRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-products') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:200',
            'category_uuid' => 'required|uuid',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'stock' => 'nullable|integer|min:0',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Mensagens customizadas de validação
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do produto é obrigatório.',
            'category_uuid.required' => 'A categoria é obrigatória.',
            'category_uuid.uuid' => 'O UUID da categoria é inválido.',
            'price.required' => 'O preço é obrigatório.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'image_url.url' => 'A URL da imagem deve ser válida.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
            'sku.unique' => 'Este SKU já está em uso.',
        ];
    }
}
