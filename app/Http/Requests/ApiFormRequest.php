<?php

namespace App\Http\Requests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Base class para FormRequests da API
 * Garante que erros de autorização sempre retornem JSON
 */
abstract class ApiFormRequest extends FormRequest
{
    /**
     * Handle a failed authorization attempt.
     * Sobrescreve para sempre lançar exception que será capturada pelo handler
     */
    protected function failedAuthorization(): void
    {
        throw new AuthorizationException('Você não tem permissão para acessar este recurso');
    }
}
