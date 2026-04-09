<?php

declare(strict_types=1);

namespace App\Application\Errors;

class EmailAlreadyExistsError extends BaseError
{
    public function __construct()
    {
        $this->code = 409;
        $this->message = 'Este e-mail já está cadastrado no sistema.';
    }
}
