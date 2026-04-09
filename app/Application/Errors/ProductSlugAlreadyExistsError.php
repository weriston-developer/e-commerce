<?php

declare(strict_types=1);

namespace App\Application\Errors;

class ProductSlugAlreadyExistsError extends BaseError
{
    public function __construct(
        public readonly string $slug,
    ) {
        $this->code = 409;
        $this->message = "Já existe um produto com o nome similar. Por favor, escolha outro nome.";
    }
}
