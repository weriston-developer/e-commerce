<?php

declare(strict_types=1);

namespace App\Application\Errors;

class DeleteProductError extends BaseError
{
    public function __construct()
    {
        $this->code = 500;
        $this->message = 'Não foi possível deletar o produto.';
    }
}
