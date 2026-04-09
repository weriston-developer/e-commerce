<?php

declare(strict_types=1);

namespace App\Application\Errors;

class CategorySlugAlreadyExistsError extends BaseError
{
    public function __construct(
        public readonly string $slug,
    ) {
        $this->code = 409;
        $this->message = "Já existe uma categoria com o nome similar. Por favor, escolha outro nome.";
    }
}
