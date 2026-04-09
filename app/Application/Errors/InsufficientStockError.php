<?php

declare(strict_types=1);

namespace App\Application\Errors;

class InsufficientStockError extends BaseError
{
    public function __construct()
    {
        $this->code = 422;
        $this->message = 'Estoque insuficiente para esta operação.';
    }
}
