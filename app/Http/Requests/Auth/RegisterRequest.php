<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiFormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class RegisterRequest extends ApiFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Qualquer pessoa pode se registrar
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

        if ($this->has('password_confirmation')) {
            $this->merge([
                'password_confirmation' => base64_decode($this->password_confirmation),
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    /**
     * Mensagens customizadas de validação
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter no mínimo 6 caracteres.',
            'password.confirmed' => 'As senhas não conferem.',
        ];
    }
}
