<?php

declare(strict_types=1);

namespace App\Application\Errors;

class DeleteCategoryError extends BaseError
{
    public function __construct()
    {
        $this->code = 500;
        $this->message = 'Não foi possível deletar a categoria.';
    }
}
