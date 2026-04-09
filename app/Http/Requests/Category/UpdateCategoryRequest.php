<?php

namespace App\Http\Requests\Category;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateCategoryRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('manage-categories') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Mensagens customizadas de validação
     */
    public function messages(): array
    {
        return [
            'name.unique' => 'Já existe uma categoria com este nome.',
            'name.max' => 'O nome não pode ter mais de 100 caracteres.',
            'description.string' => 'A descrição deve ser uma string.',
            'is_active.boolean' => 'O campo "ativo" deve ser verdadeiro ou falso.',
        ];
    }
}
