<?php

namespace JDCode\DtoPhp\Validations;

class ValidationResult
{
    public function __construct(
        public bool $success,
        public ?string $message = null
    )
    {} 

    public static function valid(): self
    {
        return new self(
            success: true
        );
    }

    public static function invalid(string $message): self
    {
        return new self(
            success: false,
            message: $message,
        );
    }
}