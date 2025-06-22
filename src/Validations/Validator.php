<?php

namespace JDCode\DtoPhp\Validations;

interface Validator
{
    public function validate(mixed $value): ValidationResult;
}