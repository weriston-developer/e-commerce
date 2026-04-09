<?php

declare(strict_types=1);

namespace App\Application\Errors;

class ValidationError extends BaseError
{
    public function __construct()
    {
        $this->code = 422;
        $this->message = 'Erro de validação nos dados fornecidos.';
    }
}
