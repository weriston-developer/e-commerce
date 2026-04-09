<?php

namespace App\Http\Requests\User;

use App\Http\Requests\ApiFormRequest;

/**
 * Request para deletar usuário
 */
class DeleteUserRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete-users') ?? false;
    }

    public function rules(): array
    {
        return [
            // UUID virá da rota
        ];
    }
}
