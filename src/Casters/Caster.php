<?php

namespace JDCode\DtoPhp\Casters;

interface Caster
{
    public function __construct(mixed $value);

    public function getValue(): mixed;
}