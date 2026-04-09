<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para representar valores monetários
 * 
 * Armazena valores em centavos (integer) para evitar problemas de precisão
 * Front-end envia/recebe em float (ex: 19.99)
 * Banco armazena em centavos (integer: 1999)
 */
readonly class Money
{
    /**
     * @param int $amountInCents Valor em centavos
     */
    private function __construct(
        private int $amountInCents
    ) {
        if ($this->amountInCents < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }
    }

    /**
     * Cria Money a partir de float (vindo do front-end)
     * 
     * @param float $amount Valor em decimal (ex: 19.99)
     * @return self
     */
    public static function fromFloat(float $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative');
        }

        // Converte para centavos (19.99 -> 1999)
        $amountInCents = (int) round($amount * 100);

        return new self($amountInCents);
    }

    /**
     * Cria Money a partir de centavos (vindo do banco)
     * 
     * @param int $amountInCents Valor em centavos (1999)
     * @return self
     */
    public static function fromCents(int $amountInCents): self
    {
        return new self($amountInCents);
    }

    /**
     * Retorna valor em centavos (para salvar no banco)
     * 
     * @return int
     */
    public function toCents(): int
    {
        return $this->amountInCents;
    }

    /**
     * Retorna valor em float (para retornar ao front-end)
     * 
     * @return float
     */
    public function toFloat(): float
    {
        return $this->amountInCents / 100;
    }

    /**
     * Retorna valor formatado como string (ex: "R$ 19,99")
     * 
     * @param string $currency Símbolo da moeda
     * @param string $decimalSeparator Separador decimal
     * @param string $thousandsSeparator Separador de milhares
     * @return string
     */
    public function format(
        string $currency = 'R$',
        string $decimalSeparator = ',',
        string $thousandsSeparator = '.'
    ): string {
        $value = number_format($this->toFloat(), 2, $decimalSeparator, $thousandsSeparator);
        return "{$currency} {$value}";
    }

    /**
     * Soma outro valor Money
     */
    public function add(Money $other): self
    {
        return new self($this->amountInCents + $other->amountInCents);
    }

    /**
     * Subtrai outro valor Money
     */
    public function subtract(Money $other): self
    {
        $result = $this->amountInCents - $other->amountInCents;
        
        if ($result < 0) {
            throw new InvalidArgumentException('Result cannot be negative');
        }

        return new self($result);
    }

    /**
     * Multiplica por quantidade
     */
    public function multiply(int $quantity): self
    {
        return new self($this->amountInCents * $quantity);
    }

    /**
     * Verifica se é maior que outro Money
     */
    public function isGreaterThan(Money $other): bool
    {
        return $this->amountInCents > $other->amountInCents;
    }

    /**
     * Verifica se é igual a outro Money
     */
    public function equals(Money $other): bool
    {
        return $this->amountInCents === $other->amountInCents;
    }

    /**
     * Retorna como string (para JSON)
     */
    public function __toString(): string
    {
        return (string) $this->toFloat();
    }

    /**
     * Para serialização JSON
     */
    public function jsonSerialize(): float
    {
        return $this->toFloat();
    }
}
