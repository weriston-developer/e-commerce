<?php

declare(strict_types=1);

namespace App\Application\Errors;

class UserNotFoundError extends BaseError
{
    public function __construct()
    {
        $this->code = 404;
        $this->message = 'Usuário não encontrado.';
    }
}
