<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class LoginRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Qualquer pessoa pode tentar fazer login
    }

    /**
     * Prepara os dados antes da validação (decodifica base64)
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('password')) {
            $this->merge([
                'password' => base64_decode($this->password),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }

    /**
     * Mensagens customizadas de validação
     */
    public function messages(): array
    {
        return [
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'password.required' => 'A senha é obrigatória.',
        ];
    }
}
