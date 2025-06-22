<?php

namespace JDCode\DtoPhp\Validations;

class Email implements Validator
{
    public function __construct(private ?string $message = null)
    {}

    public function validate(mixed $value): ValidationResult
    {
        $message = ($this->message != null) ? $this->message : $this->messageError();

        if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
            return new ValidationResult(success: false, message: $message);
        }
        
        return new ValidationResult(success: true);
    }

    private function messageError(): string
    {
        return "Value is not a valid email";
    }
}