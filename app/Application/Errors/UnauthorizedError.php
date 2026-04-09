<?php

declare(strict_types=1);

namespace App\Application\Errors;

class UnauthorizedError extends BaseError
{
    public function __construct()
    {
        $this->code = 401;
        $this->message = 'Não autorizado. Token inválido ou expirado.';
    }
}
