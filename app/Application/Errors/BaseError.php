<?php

declare(strict_types=1);

namespace App\Application\Errors;


class BaseError implements IError
{
    protected int $code;
    protected string $message;
    protected ?string $systemPrefix  = null;

    public function __construct(
        protected ?string $systemError   = null,
        protected ?string $systemMessage = null,
    ) {
        $this->systemError = $systemError;
        $this->systemMessage = $systemMessage;
    }
    
    public function getCode(): int
    {
        return $this->code;
    }

    public function getMessage(): string
    {
        return ($this->systemPrefix && $this->systemError)
            ? $this->systemPrefix . "$this->systemError: $this->message"
            : $this->message;
    }

    public function getSystemPrefix(): ?string
    {
        return $this->systemPrefix;
    }

    public function getSystemError(): ?string
    {
        return $this->systemError;
    }

    public function getSystemMessage(): ?string
    {
        return $this->systemMessage;
    }
}
