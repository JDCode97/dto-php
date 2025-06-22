<?php

namespace JDCode\DtoPhp\Casters;

class CastArrayWithObject implements Caster
{
    public function __construct(private mixed $value)
    {
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}