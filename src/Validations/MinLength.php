<?php

namespace JDCode\DtoPhp\Validations;

use JDCode\DtoPhp\Validations\ValidationResult;
use JDCode\DtoPhp\Validations\Validator;

class MinLength implements Validator
{
    public function __construct(private int $length, private ?string $message = null)
    {}

    public function validate(mixed $value): ValidationResult
    {
        $message = ($this->message != null) ? $this->message : $this->messageError();

        if(strlen($value) < $this->length){
            return new ValidationResult(success: false, message: $message);
        }
        
        return new ValidationResult(success: true);
    }

    private function messageError(): string
    {
        return "Value must be greater than {$this->length} characters";
    }
}