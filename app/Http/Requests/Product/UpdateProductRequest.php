<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateProductRequest extends ApiFormRequest
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
            'name' => 'sometimes|string|max:200',
            'category_uuid' => 'sometimes|uuid|exists:categories,uuid',
            'price' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Mensagens customizadas de validação
     */
    public function messages(): array
    {
        return [
            'name.string' => 'O nome deve ser texto.',
            'category_uuid.uuid' => 'UUID de categoria inválido.',
            'category_uuid.exists' => 'Categoria não encontrada.',
            'price.numeric' => 'O preço deve ser um número.',
            'price.min' => 'O preço não pode ser negativo.',
            'image_url.url' => 'A URL da imagem deve ser válida.',
            'stock.integer' => 'O estoque deve ser um número inteiro.',
            'stock.min' => 'O estoque não pode ser negativo.',
            'is_active.boolean' => 'O campo ativo deve ser verdadeiro ou falso.',
        ];
    }
}
