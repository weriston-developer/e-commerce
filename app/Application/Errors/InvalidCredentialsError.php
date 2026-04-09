<?php

declare(strict_types=1);

namespace App\Application\Errors;

class InvalidCredentialsError extends BaseError
{
    public function __construct()
    {
        $this->code = 401;
        $this->message = 'Credenciais inválidas. Verifique e-mail e senha.';
    }
}
