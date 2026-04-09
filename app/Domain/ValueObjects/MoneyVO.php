<?php

namespace App\Domain\ValueObjects;

class MoneyVO extends DecimalVO
{
    public function __construct(string|float|int|null $input = 0, int $scale = 4)
    {
        parent::__construct($input, $scale);
    }

    public function format(): string
    {
        return "R$ ".number_format($this->toFloat(), 2, ',', '.');
    }
}
