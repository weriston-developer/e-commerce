<?php

declare(strict_types=1);

namespace App\Application\Exceptions;

use App\Application\Errors\BaseError;
use Exception;

class BaseException extends Exception
{
    private BaseError $error;

    public function __construct(string|BaseError $module)
    {
        $this->error = $module instanceof BaseError ? $module : resolve($module);
        parent::__construct($this->error->getMessage(), $this->error->getCode());
    }

    public function getError(): BaseError
    {
        return $this->error;
    }
}
