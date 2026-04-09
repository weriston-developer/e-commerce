<?php

declare(strict_types=1);

namespace App\Application\Errors;

use App\Application\Errors\BaseError;

class DecimalValueInvalidError extends BaseError
{
    public function __construct()
    {
        $this->code    = 422;
        $this->message = 'Número inválido.';
    }
}
