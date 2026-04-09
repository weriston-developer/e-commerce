<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

/**
 * Request para listar usuários
 */
class ListUsersRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete-users') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
