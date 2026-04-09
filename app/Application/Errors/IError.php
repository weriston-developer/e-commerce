<?php

declare(strict_types=1);

namespace App\Application\Errors;

interface IError
{
    public function getCode(): int;

    public function getMessage(): string;
}
