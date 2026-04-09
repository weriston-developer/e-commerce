<?php

declare(strict_types=1);

namespace App\Application\Errors;

class CategoryNotExistsError extends BaseError
{
    public function __construct()
    {
        $this->code = 400;
        $this->message = "A categoria não existe ou foi removida.";
    }
}
