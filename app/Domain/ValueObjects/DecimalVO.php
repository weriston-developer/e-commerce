<?php

namespace App\Domain\ValueObjects;

use App\Application\Errors\DecimalValueInvalidError;
use App\Application\Exceptions\BaseException;

class DecimalVO
{
    protected string $value;
    protected int $scale = 4;
    public function __construct(string|float|int|null $input = 0, int $scale = 4)
    {
        $this->scale = $scale;
        if (is_null($input)) {
            $input = 0;
        }
        if (!is_numeric($input)) {
            throw new BaseException(DecimalValueInvalidError::class);
        }
        $input = number_format((float)$input, $scale, '.', '');
        $this->value = bcadd((string)$input, '0', $this->scale);
    }

    public function add(DecimalVO $other): static
    {
        return new static(bcadd($this->value, $other->value, $this->scale));
    }

    public function subtract(DecimalVO $other): static
    {
        return new static(bcsub($this->value, $other->value, $this->scale));
    }

    public function multiply(DecimalVO $other): static
    {
        return new static(bcmul($this->value, $other->value, $this->scale));
    }

    public function divide(DecimalVO $other): static
    {
        if ((float)$other->value === 0.0) {
            throw new \DivisionByZeroError("Tentativa de divisão por zero.");
        }
        return new static(bcdiv($this->value, $other->value, $this->scale));
    }

    public function equals(DecimalVO $other): bool
    {
        return bccomp($this->toFloat(), $other->toFloat(), $this->scale) === 0;
    }

    public function notEquals(DecimalVO $other): bool
    {
        return bccomp($this->toFloat(), $other->toFloat(), $this->scale) !== 0;
    }

    public function largerThan(DecimalVO $other): bool
    {
        return bccomp($this->toFloat(), $other->toFloat(), $this->scale) === 1;
    }

    public function lessThan(DecimalVO $other): bool
    {
        return bccomp($this->toFloat(), $other->toFloat(), $this->scale) === -1;
    }

    public function lessOrEquals(DecimalVO $other): bool
    {
        return $this->lessThan($other) || $this->equals($other);
    }

    public function largerOrEquals(DecimalVO $other): bool
    {
        return $this->largerThan($other) || $this->equals($other);
    }

    public function between(DecimalVO $min, DecimalVO $max): bool
    {
        return !$this->lessThan($min) && !$this->largerThan($max);
    }

    public function isNegative(): bool
    {
        return bccomp($this->value, '0', $this->scale) === -1;
    }

    public function isPositive(): bool
    {
        return bccomp($this->value, '0', $this->scale) === 1;
    }

    public function toFloat(): float
    {
        return round((float)$this->value, 2);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
