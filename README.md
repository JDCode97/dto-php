[![PHP version](https://img.shields.io/badge/PHP-%3E%3D8.0-8892BF.svg?style=flat-square)](http://php.net)

# Dto-Php

Simple and flexible Data Transfer Object library with validations

## Installation

### Composer

```
composer require jdcode/dto-php
```

## Usage
Class Dto
```php
use JDCode\DtoPhp\DtoPhp;

class StoreUserDto extends DtoPhp
{
    public string $username;
    public string $password;
    public int $age;
}
```

In your UseCase class
```php
$dto = new StoreUserDto([
    'username' => 'admin',
    'password => '123456'
    'age' => 99
]);
```

## Validation
Currently it only has 3 basic validations

Class Dto
```php
use JDCode\DtoPhp\DtoPhp;
use JDCode\DtoPhp\Validations\MinLength;
use JDCode\DtoPhp\Validations\MaxLength;
use JDCode\DtoPhp\Validations\Email;

class StoreUserDto extends DtoPhp
{
    #[MinLength(length: 3), MaxLength(length: 10)]
    public string $username;

    public string $password;

    #[Email]
    public string $email

    public int $age;
}
```
You can use `$dto->validated()` to know if the validation was correct and `$dto->getErrors()` to get the error messages.

## Type Class object

If your property type is a DTO object, just placing it will automatically cast it.

Class Dto
```php
use JDCode\DtoPhp\DtoPhp;

class StoreUserDto extends DtoPhp
{
    public string $username;
    public string $password;
    public int $age;
    public RoleDto $role
}
```

```php
use JDCode\DtoPhp\DtoPhp;

class RoleDto extends DtoPhp
{
    public int $id;
    public string $name;
}
```

In your UseCase class
```php
$dto = new StoreUserDto([
    'username' => 'admin',
    'password => '123456'
    'age' => 99,
    'roles' => [
        'id' => 1,
        'name' => 'Administrator'
    ]
]);
```
## Array Object

If you need an object array, just by placing the corresponding attribute you will get your object array.

```php
use JDCode\DtoPhp\DtoPhp;

class RoleDto extends DtoPhp
{
    public int $id;
    public string $name;
}
```

```php
use JDCode\DtoPhp\DtoPhp;
use JDCode\DtoPhp\Casters\CastArrayWithObject;

class StoreUserDto extends DtoPhp
{
    public string $username;
    public string $password;

    #[CastArrayWithObject(Role::class)]
    public array $roles;
}
```

In your UseCase class
```php
$dto = new StoreUserDto([
    'username' => 'admin',
    'password => '123456'
    'age' => 99,
    'roles' => [
       [
            'id' => 1,
            'name' => 'Administrator'
       ],
       [
            'id' => 2,
            'name' => 'Moderator'
       ]
    ]
]);

var_dump($dto->roles) // returns RoleDto[]
```
## License

MIT