<?php

declare(strict_types=1);

namespace App\Application\Errors;

class MoneyValueNegativeError extends BaseError
{
    public function __construct()
    {
        $this->code = 422;
        $this->message = 'O valor monetário não pode ser negativo.';
    }
}
