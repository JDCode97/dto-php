<?php

namespace JDCode\DtoPhp;

use ReflectionClass;
use ReflectionProperty;
use JDCode\DtoPhp\Exceptions\CannotAssignPropertyException;
use JDCode\DtoPhp\Exceptions\MissingPropertyException;

class DtoPhp
{
    private string $nameClassCalled;
    private array $errors = [];

    public function __construct(?array $data = null)
    {
        $this->errors = [];

        if($data != null){
            $this->init($data);        
        }
    }

    public function validated(): bool
    {
        return (count($this->errors) == 0) ? true : false;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function init(array $data): void
    {
        $dataProperties = [];

        $this->nameClassCalled = get_called_class();

        $reflection = new ReflectionClass($this->nameClassCalled);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach($properties as $property){
            if($property->isStatic() || $property->isPrivate()){
                continue;
            }

            $dataProperties[$property->getName()] = null;

            $this->processProperty($property, $data);
        }
    }

    private function set($name, $value = null): self
    {
        if(is_array($name)){
            foreach($name as $k => $v){
                $this->set($k, $v);
            }
        }else{
            $this->{$name} = $value;
        }

        return $this;
    }

    private function processProperty(object $property, array $data)
    {
        $type = $property->getType();
        $name = $property->getName();

        $valueProcessed = null;

        if(!array_key_exists($name, $data)){
            if(!$property->getType()->allowsNull()){
                $message = 'Missing '.$this->nameClassCalled."::$".$name.' property';
                throw new MissingPropertyException(message: $message);
            }
        }else{
            if($type != null){
                $valueProcessed = $this->processvalue($name, $type, $data[$name]);
            }
        }

        if(count($property->getAttributes()) > 0){
            $valueProcessed = $this->processAttributes($name, $valueProcessed, $property->getAttributes());
        }

        $this->set($name, $valueProcessed);
    }

    private function processvalue(string $nameProperty, object $type, mixed $value): mixed
    {
        $typesAllowed = $this->getTypesAllowed($type);
        $typeValue = $this->getTypeValue($value);
        
        if(!in_array($typeValue, $typesAllowed)){
            for($i = 0; $i < count($typesAllowed); $i++){
                if(class_exists($typesAllowed[$i])){
                    return new $typesAllowed[$i]($value);
                }
            }

            $stringTypesAllowed = implode('|', $typesAllowed);
            $message = "Cannot assign ".$typeValue." to property ".$this->nameClassCalled."::$".$nameProperty." of type ".$stringTypesAllowed;

            throw new CannotAssignPropertyException(message: $message);
        }

        return $value;
    }

    private function getTypesAllowed(object $type): array
    {
        $methodsAllowed = [];

        if(method_exists($type, 'getTypes')){
            foreach($type->getTypes() as $reflectonType){
                array_push($methodsAllowed, $reflectonType->getName());
            }
        }else{
            array_push($methodsAllowed, $type->getName());
        }

        if($type->allowsNull()){
            if(!in_array('null', $methodsAllowed)){
                array_push($methodsAllowed, 'null');
            }
        }

        return $methodsAllowed;
    }

    private function getTypeValue(mixed $value): string
    {
        $typeValue = gettype($value);

        return match($typeValue){
            'integer'   => 'int',
            'boolean'   => 'bool',
            'double'    => 'float',
            'NULL'      => 'null',
            default     => $typeValue
        };
    }

    private function processAttributes(string $nameProperty, mixed $valueProperty, array $attributes): mixed
    {
        foreach($attributes as $valueAttribute){
            $name = $valueAttribute->getName();
            $arguments = $valueAttribute->getArguments();

            $reflectionClass = new ReflectionClass($name);

            $interfaces = $reflectionClass->getInterfaceNames();

            if(count($interfaces) > 0){
                if(in_array('JDCode\DtoPhp\Validations\Validator', $interfaces)){
                    $newInstance =  $reflectionClass->newInstanceArgs($arguments);

                    $result = $newInstance->validate($valueProperty);

                    if(!$result->success){
                        $this->errors[$nameProperty][] = $result->message;
                    }
                }else if(in_array('JDCode\DtoPhp\Casters\Caster', $interfaces)){
                    $newInstance =  $reflectionClass->newInstanceArgs($arguments);

                    if(class_exists($newInstance->getValue())){
                        $arrayClass = [];
    
                        $nameClassCaster = $newInstance->getValue();

                        for($i = 0; $i < count($valueProperty); $i++){
                            $arrayClass[] = new $nameClassCaster($valueProperty[$i]);
                        }

                        $valueProperty = $arrayClass;
                    }
                }
            }
        }

        return $valueProperty;
    }
}