<?php

declare(strict_types=1);

namespace App\Application\Errors;

class CannotDeleteAdminUserError extends BaseError
{
    public function __construct()
    {
        $this->code = 403;
        $this->message = 'Não é possível excluir usuários administradores.';
    }
}
