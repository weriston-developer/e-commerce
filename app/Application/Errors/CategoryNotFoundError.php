<?php

declare(strict_types=1);

namespace App\Application\Errors;

class CategoryNotFoundError extends BaseError
{
    public function __construct()
    {
        $this->code = 404;
        $this->message = 'Categoria não encontrada.';
    }
}
